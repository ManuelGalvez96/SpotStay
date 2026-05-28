# Generacion de PDF en SpotStay

Este documento explica como funciona la generacion de PDF de contratos en SpotStay, desde la vista del arrendador hasta la integracion con PdfMonkey y el guardado de la URL del documento.

## Resumen rapido

El proyecto genera contratos PDF a partir de los datos de un alquiler. El flujo principal es este:

1. El usuario abre el modulo de contratos.
2. El sistema comprueba si ya existe una URL de PDF guardada en la base de datos.
3. Si el PDF existe y sigue siendo valido, se reutiliza.
4. Si no existe o ya no es valido, se genera uno nuevo con PdfMonkey.
5. La URL resultante se guarda en `tbl_contrato` para reutilizarla la siguiente vez.

Hay dos puntos principales que crean contratos PDF:

- El panel de arrendador, al abrir o descargar un contrato.
- El panel de admin, al crear contratos desde el flujo de alquileres.

---

## Componentes implicados

### Controladores

- `app/Http/Controllers/Arrendador/ContratoController.php`
- `app/Http/Controllers/Admin/AlquilerController.php`

### Servicio externo

- `app/Services/PdfMonkeyService.php`

### Configuracion

- `config/pdfmonkey.php`

### Vista principal del arrendador

- `resources/views/arrendador/contratos.blade.php`

---

## Flujo desde el modulo de arrendador

### 1. Vista de contratos

En `resources/views/arrendador/contratos.blade.php` se muestra una tabla con:

- ID del contrato
- Propiedad asociada
- Inquilino
- Estado de firma del arrendador
- Estado de firma del inquilino
- Estado general
- Acciones

La accion para ver o descargar el PDF se muestra solo si ya existe una URL guardada:

```blade
@if (!empty($contrato->url_pdf_contrato))
    <a class="btn-ver" href="{{ route('arrendador.contratos.descargar-pdf', ['id' => $contrato->id_contrato, 'arrendador_id' => $arrendadorId]) }}" target="_blank">Ver PDF</a>
@endif
```

Eso significa que el enlace no genera el PDF por si solo desde la vista; llama al controlador para decidir si reutiliza uno ya creado o si debe regenerarlo.

### 2. Descarga o visualizacion del PDF

El metodo principal es `descargarPDF()` en `Arrendador/ContratoController.php`.

Su comportamiento es el siguiente:

- Busca el contrato solicitado por ID.
- Verifica que pertenezca al arrendador autenticado.
- Comprueba si ya hay una URL de PDF guardada en la columna correspondiente.
- Si la URL existe y sigue siendo valida, redirige directamente al archivo.
- Si no existe o esta rota, genera el PDF de nuevo.
- Guarda la nueva URL en la base de datos.
- Redirige al PDF generado.

Ejemplo simplificado del flujo:

```php
if (!empty($contrato->url_pdf_contrato)) {
    if ($this->esUrlPdfLocalExistente($contrato->url_pdf_contrato)) {
        return redirect()->away($this->normalizarUrlPdf($contrato->url_pdf_contrato));
    }
}

$urlPdf = $this->generarPDFOnDemand($contrato->id_alquiler);
```

---

## Como se genera el PDF

La generacion real se hace en `generarPDFOnDemand()`.

### Paso 1. Obtener los datos del contrato

El controlador construye una consulta con joins para reunir todos los datos necesarios:

- datos del arrendador
- datos del inquilino
- datos de la propiedad
- fechas del alquiler
- precio mensual

Ejemplo:

```php
$datosAlquiler = DB::table('tbl_alquiler as a')
    ->join('tbl_propiedad as p', 'a.id_propiedad_fk', '=', 'p.id_propiedad')
    ->join('tbl_usuario as arrendador', 'p.id_arrendador_fk', '=', 'arrendador.id_usuario')
    ->join('tbl_usuario as inquilino', 'a.id_inquilino_fk', '=', 'inquilino.id_usuario')
    ->where('a.id_alquiler', $idAlquiler)
    ->select(
        'a.id_alquiler',
        'a.fecha_inicio_alquiler',
        'a.fecha_fin_alquiler',
        'arrendador.nombre_usuario as nombre_arrendador',
        'arrendador.email_usuario as email_arrendador',
        'inquilino.nombre_usuario as nombre_inquilino',
        'inquilino.email_usuario as email_inquilino',
        'p.titulo_propiedad',
        'p.direccion_propiedad',
        'p.ciudad_propiedad',
        'p.precio_propiedad'
    )
    ->first();
```

### Paso 2. Calcular datos derivados

Antes de enviar el contenido a PdfMonkey, el sistema calcula la fianza.

En este proyecto se usa esta regla:

- fianza = precio mensual x 2

Ejemplo:

```php
$precioMensual = (float) ($datosAlquiler->precio_propiedad ?? 0);
$fianza = $precioMensual * 2;
```

### Paso 3. Construir el payload

Se crea un array con las claves que PdfMonkey necesita para rellenar la plantilla:

```php
$datosContrato = [
    'nombre_arrendador' => $datosAlquiler->nombre_arrendador,
    'email_arrendador' => $datosAlquiler->email_arrendador,
    'nombre_inquilino' => $datosAlquiler->nombre_inquilino,
    'email_inquilino' => $datosAlquiler->email_inquilino,
    'titulo_propiedad' => $datosAlquiler->titulo_propiedad,
    'direccion_propiedad' => $datosAlquiler->direccion_propiedad,
    'ciudad_propiedad' => $datosAlquiler->ciudad_propiedad,
    'precio_mensual' => number_format($precioMensual, 2, '.', ''),
    'fianza' => number_format($fianza, 2, '.', ''),
    'fecha_inicio' => Carbon::parse($datosAlquiler->fecha_inicio_alquiler)->format('d/m/Y'),
    'fecha_fin' => $datosAlquiler->fecha_fin_alquiler
        ? Carbon::parse($datosAlquiler->fecha_fin_alquiler)->format('d/m/Y')
        : 'Indefinida',
    'fecha_generacion' => Carbon::now()->format('d/m/Y'),
];
```

### Paso 4. Llamar a PdfMonkey

El controlador usa el servicio `PdfMonkeyService` para enviar los datos a la API:

```php
$respuesta = $pdfMonkey->crearDocumentoSincronizado(
    $datosContrato,
    $pdfMonkey->construirMeta([], 'contrato_' . $idAlquiler . '.pdf')
);
```

La llamada es sincronica, lo que significa que el sistema espera a que PdfMonkey termine de generar el documento antes de continuar.

### Paso 5. Obtener la URL del documento

La API puede devolver la URL de dos formas:

- directamente en `document_card.download_url`
- o a traves del ID del documento, pidiendo la tarjeta despues

Ejemplo:

```php
if (isset($respuesta['document_card']['download_url'])) {
    return $respuesta['document_card']['download_url'];
}

if (isset($respuesta['document']) && isset($respuesta['document']['id'])) {
    return $pdfMonkey->obtenerUrlDescarga($respuesta['document']['id']);
}
```

### Paso 6. Guardar la URL en base de datos

Cuando se obtiene la URL final, se guarda en `tbl_contrato` para no repetir la generacion en el siguiente acceso.

```php
$datosActualizar[$columnas['url_pdf']] = $urlPdf;
```

---

## Flujo desde el panel admin

El panel administrativo tambien crea PDFs al generar contratos de alquiler.

### Metodo implicado

- `Admin/AlquilerController.php -> generarContratoConPDF()`

### Comportamiento

1. Reune los datos del alquiler.
2. Calcula el importe de la fianza.
3. Construye el mismo payload de PdfMonkey.
4. Llama a `crearDocumentoSincronizado()`.
5. Guarda el contrato en `tbl_contrato` con la URL generada.

Ejemplo de guardado:

```php
$datosContratoBD = [
    'id_alquiler_fk' => $alquiler->id_alquiler,
    'url_pdf_contrato' => $urlPdf ?? '',
    'estado_contrato' => 'pendiente',
    'creado_contrato' => Carbon::now(),
];
```

Esto significa que el contrato puede nacer ya con su PDF asociado desde el flujo admin, sin esperar a que el arrendador lo genere manualmente.

---

## Como funciona PdfMonkeyService

`PdfMonkeyService` encapsula toda la comunicacion con la API.

### `estaConfigurado()`

Comprueba que existan los valores necesarios en el entorno:

- `PDFMONKEY_API_KEY`
- `PDFMONKEY_TEMPLATE_ID`

Si falta alguno, el sistema no intenta generar el PDF.

### `crearDocumentoSincronizado()`

Hace una peticion POST a `/documents/sync`.

Ese endpoint devuelve el documento ya preparado o la informacion necesaria para obtener la URL de descarga.

### `obtenerUrlDescarga()`

Consulta la tarjeta del documento y extrae `download_url`.

### `construirMeta()`

Añade metadatos extra al documento, como el nombre de archivo:

```php
$pdfMonkey->construirMeta([], 'contrato_15.pdf');
```

### `solicitud()`

Configura el cliente HTTP con:

- `base_url`
- `api_key`
- `timeout`
- `connect_timeout`
- verificacion SSL

---

## Configuracion necesaria

El archivo `config/pdfmonkey.php` lee estas variables de entorno:

- `PDFMONKEY_BASE_URL`
- `PDFMONKEY_API_KEY`
- `PDFMONKEY_TEMPLATE_ID`
- `PDFMONKEY_TIMEOUT`
- `PDFMONKEY_CONNECT_TIMEOUT`
- `PDFMONKEY_VERIFY_SSL`
- `PDFMONKEY_DEFAULT_STATUS`
- `PDFMONKEY_FILENAME_PREFIX`

Ejemplo de configuracion:

```env
PDFMONKEY_BASE_URL=https://api.pdfmonkey.io/api/v1
PDFMONKEY_API_KEY=tu_api_key
PDFMONKEY_TEMPLATE_ID=tu_template_id
PDFMONKEY_TIMEOUT=30
PDFMONKEY_CONNECT_TIMEOUT=10
PDFMONKEY_VERIFY_SSL=true
PDFMONKEY_DEFAULT_STATUS=pending
PDFMONKEY_FILENAME_PREFIX=spotstay
```

Si la API Key o el Template ID faltan, la generacion se cancela de forma segura.

---

## Reutilizacion del PDF

El sistema no genera el PDF cada vez que se abre el contrato.

Primero comprueba si la URL guardada sigue viva:

- Si es una URL local, verifica que el archivo exista.
- Si es una URL remota, intenta comprobar si no ha caducado.

Si la URL sigue siendo valida, la reutiliza.

Esto reduce llamadas innecesarias a PdfMonkey y hace mas rapido el acceso al contrato.

---

## Firmas del contrato

La generacion del PDF es independiente de la firma, pero el contrato se gestiona junto con estos estados:

- `firmado_arrendador`
- `firmado_inquilino`
- `estado_contrato`

En el modulo de arrendador, el usuario puede ver si ya firmo o no, y si ya hay PDF disponible.

La firma del arrendador actualiza el estado del contrato, pero no regenera el PDF.

---

## Casos de error

### PdfMonkey no configurado

Si faltan credenciales, el sistema devuelve `null` o no hace la llamada.

### Contrato no encontrado

Si el contrato no pertenece al arrendador o no existe, se devuelve un error 404.

### URL rota o caducada

Si el PDF guardado ya no es accesible, el sistema intenta regenerarlo.

### Fallo de red o API externa

Si PdfMonkey responde con error o la conexion falla, el controlador captura la excepcion y devuelve un mensaje de error.

---

## Ejemplo del flujo completo

Caso real:

1. El arrendador abre el modulo de contratos.
2. Pulsa `Ver PDF` en un contrato.
3. El controlador busca `url_pdf_contrato`.
4. Si no existe, llama a PdfMonkey.
5. PdfMonkey devuelve una URL de descarga.
6. El sistema la guarda en `tbl_contrato`.
7. El navegador redirige al PDF.

---

## Puntos importantes para mantenimiento

- La generacion depende de la tabla `tbl_contrato` y de su columna de URL.
- El payload enviado a PdfMonkey debe coincidir con las variables del template.
- Si cambian los nombres de columnas, el controlador ya tiene logica para resolver columnas alternativas con `Schema::hasColumn`.
- El flujo del arrendador reutiliza documentos ya generados para evitar duplicados.

---

## Archivos relacionados

- `app/Http/Controllers/Arrendador/ContratoController.php`
- `app/Http/Controllers/Admin/AlquilerController.php`
- `app/Services/PdfMonkeyService.php`
- `config/pdfmonkey.php`
- `resources/views/arrendador/contratos.blade.php`

---

## Conclusiones

La generacion de PDF en SpotStay esta pensada como un proceso centralizado y reutilizable. La aplicacion:

- recopila datos del contrato,
- los envia a PdfMonkey,
- obtiene una URL de descarga,
- la guarda en la base de datos,
- y reutiliza el mismo documento cuando es posible.

Eso evita generar el mismo PDF varias veces y hace que el acceso desde el panel del arrendador sea mas rapido.
