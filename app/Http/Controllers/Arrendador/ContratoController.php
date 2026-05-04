<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use App\Services\PdfMonkeyService;

class ContratoController extends Controller
{
    public function inicio(Request $request): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnas = $this->obtenerColumnasContrato();

        $arrendador = DB::table('tbl_usuario')
            ->where('id_usuario', $arrendadorId)
            ->select('id_usuario', 'nombre_usuario', 'email_usuario')
            ->first();

        $contratos = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'c.id_contrato',
                'a.id_alquiler',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'inquilino.nombre_usuario as nombre_inquilino',
                DB::raw($this->seleccionarColumnaContrato($columnas['url_pdf'], 'url_pdf_contrato', "''")),
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_arrendador'], 'firmado_arrendador', '0')),
                DB::raw($this->seleccionarColumnaContrato($columnas['fecha_firma_arrendador'], 'fecha_firma_arrendador', 'NULL')),
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_inquilino'], 'firmado_inquilino', '0')),
                DB::raw($this->seleccionarColumnaContrato($columnas['fecha_firma_inquilino'], 'fecha_firma_inquilino', 'NULL')),
                DB::raw($this->seleccionarColumnaContrato($columnas['estado'], 'estado_contrato', "'pendiente'"))
            )
            ->orderByDesc('c.id_contrato')
            ->paginate(10);

        $total = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->count('c.id_contrato');

        $firmados = $this->contarFirmados($arrendadorId, $columnas);
        $pendientes = max(0, $total - $firmados);

        return view('arrendador.contratos', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'contratos' => $contratos,
            'totales' => [
                'total' => $total,
                'firmados' => $firmados,
                'pendientes' => $pendientes,
            ],
        ]);
    }

    public function firmarArrendador(Request $request, int $id): JsonResponse
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnas = $this->obtenerColumnasContrato();

        $contrato = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('c.id_contrato', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'c.id_contrato',
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_arrendador'], 'firmado_arrendador', '0')),
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_inquilino'], 'firmado_inquilino', '0'))
            )
            ->first();

        if (!$contrato) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro el contrato.',
            ], 404);
        }

        if ((bool) $contrato->firmado_arrendador) {
            return response()->json([
                'success' => true,
                'message' => 'El contrato ya estaba firmado por el arrendador.',
                'estado' => (bool) $contrato->firmado_inquilino ? 'firmado' : 'pendiente',
            ]);
        }

        $datosActualizar = [];

        if ($columnas['firmado_arrendador']) {
            $datosActualizar[$columnas['firmado_arrendador']] = true;
        }

        if ($columnas['fecha_firma_arrendador']) {
            $datosActualizar[$columnas['fecha_firma_arrendador']] = Carbon::now();
        }

        if ($columnas['ip_firma_arrendador']) {
            $datosActualizar[$columnas['ip_firma_arrendador']] = $request->ip();
        }

        $estadoNuevo = (bool) $contrato->firmado_inquilino ? 'firmado' : 'pendiente';

        if ($columnas['estado']) {
            $datosActualizar[$columnas['estado']] = $estadoNuevo;
        }

        if ($columnas['actualizado']) {
            $datosActualizar[$columnas['actualizado']] = Carbon::now();
        }

        DB::table('tbl_contrato')
            ->where('id_contrato', $id)
            ->update($datosActualizar);

        return response()->json([
            'success' => true,
            'message' => 'Contrato firmado por el arrendador.',
            'estado' => $estadoNuevo,
            'firmado_arrendador' => true,
        ]);
    }

    /**
     * GESTIONA LA DESCARGA O VISUALIZACIÓN DEL PDF DE UN CONTRATO.
     *
     * Flujo lógico:
     * 1. Busca si el contrato ya tiene una URL de PDF guardada en la BD.
     * 2. Si existe URL, verifica si sigue siendo válida (que no haya caducado).
     *    - Si es válida: Redirige al usuario directamente (ahorra tiempo y recursos).
     *    - Si NO es válida o no existe: Genera un PDF nuevo desde cero.
     * 3. Si genera uno nuevo, guarda la nueva URL en la BD para la próxima vez.
     * 4. Redirige al usuario a la URL del PDF.
     */
    public function descargarPDF(Request $request, int $id)
    {
        // Obtenemos el ID del arrendador y las columnas válidas de la tabla contrato
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnas = $this->obtenerColumnasContrato();

        // 1. CONSULTA A BD: Buscamos si ya tenemos un PDF generado para este contrato.
        // Hacemos JOIN para verificar que el contrato pertenezca a este arrendador.
        $contrato = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('c.id_contrato', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'c.id_contrato',
                'a.id_alquiler',
                DB::raw($this->seleccionarColumnaContrato($columnas['url_pdf'], 'url_pdf_contrato', "''"))
            )
            ->first();

        if (!$contrato) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el contrato.',
            ], 404);
        }

        // 2. VERIFICACIÓN DE CACHE: ¿Tenemos ya una URL guardada?
        if (!empty($contrato->url_pdf_contrato)) {
            // Verificamos que el archivo exista (si es local) o que el enlace no haya caducado (si es remoto)
            if ($this->esUrlPdfLocalExistente($contrato->url_pdf_contrato)) {
                // ¡Éxito! La URL es buena. Enviamos al usuario allí directamente.
                return redirect()->away($this->normalizarUrlPdf($contrato->url_pdf_contrato));
            }

            // Si llegamos aquí, la URL existe pero está rota o caducada.
            Log::warning("PDF guardado pero no disponible, se regenerará", [
                'contrato_id' => $id,
                'url_pdf_contrato' => $contrato->url_pdf_contrato,
            ]);
        }

        // 3. GENERACIÓN: Si no hay URL o está caducada, creamos el PDF de nuevo.
        Log::info("Generando PDF on-demand para alquiler: {$contrato->id_alquiler}");
        
        $urlPdf = $this->generarPDFOnDemand($contrato->id_alquiler);
        
        if (!$urlPdf) {
            Log::error("Fallo al generar PDF on-demand para alquiler: {$contrato->id_alquiler}");
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el PDF del contrato. Por favor, contacta con soporte.',
            ], 500);
        }

        // 4. ACTUALIZACIÓN: Guardamos la nueva URL en la base de datos para no tener que generarlo la próxima vez.
        $datosActualizar = [];
        if ($columnas['url_pdf']) {
            $datosActualizar[$columnas['url_pdf']] = $urlPdf;
        }
        if ($columnas['actualizado']) {
            $datosActualizar[$columnas['actualizado']] = Carbon::now();
        }

        if (!empty($datosActualizar)) {
            DB::table('tbl_contrato')
                ->where('id_contrato', $id)
                ->update($datosActualizar);
            
            Log::info("PDF guardado para contrato: {$id}");
        }

        // 5. REDIRECCIÓN FINAL: Enviamos al usuario al PDF recién generado.
        return redirect()->away($urlPdf);
    }

    private function esUrlPdfLocalExistente(string $urlPdf): bool
    {
        if (preg_match('#^https?://#i', $urlPdf)) {
            return $this->esUrlRemotaVigente($urlPdf);
        }

        $rutaRelativa = ltrim($urlPdf, '/\\');

        return File::exists(public_path($rutaRelativa));
    }

    private function esUrlRemotaVigente(string $urlPdf): bool
    {
        $componentes = parse_url($urlPdf);
        if (!$componentes || empty($componentes['query'])) {
            return true;
        }

        parse_str($componentes['query'], $parametros);
        $ahora = Carbon::now('UTC')->timestamp;
        $margenSeguridad = 30;

        if (isset($parametros['Expires']) && is_numeric($parametros['Expires'])) {
            return $ahora < (((int) $parametros['Expires']) - $margenSeguridad);
        }

        $xAmzDate = $parametros['X-Amz-Date'] ?? null;
        $xAmzExpires = $parametros['X-Amz-Expires'] ?? null;
        if ($xAmzDate && $xAmzExpires && is_numeric($xAmzExpires)) {
            $fechaFirma = Carbon::createFromFormat('Ymd\\THis\\Z', $xAmzDate, 'UTC');
            if ($fechaFirma !== false) {
                $expiraEn = $fechaFirma->copy()->addSeconds((int) $xAmzExpires)->timestamp;

                return $ahora < ($expiraEn - $margenSeguridad);
            }
        }

        return true;
    }

    private function normalizarUrlPdf(string $urlPdf): string
    {
        if (preg_match('#^https?://#i', $urlPdf)) {
            return $urlPdf;
        }

        return url('/' . ltrim($urlPdf, '/\\'));
    }

    /**
     * GENERA UN PDF NUEVO LLAMANDO A LA API DE PDFMONKEY.
     *
     * Este método se encarga de:
     * 1. Recopilar todos los datos necesarios del alquiler (inquilino, arrendador, propiedad, precios).
     * 2. Formatear estos datos en un array (Payload) compatible con la plantilla HTML de PdfMonkey.
     * 3. Enviar una petición POST a la API de PdfMonkey para generar el documento.
     * 4. Extraer y devolver la URL de descarga del PDF generado.
     *
     * @param int $idAlquiler El ID del alquiler para el cual se genera el contrato.
     * @return string|null La URL de descarga del PDF o null si falla.
     */
    private function generarPDFOnDemand(int $idAlquiler): ?string
    {
        try {
            // Inicializamos el servicio de conexión con la API
            $pdfMonkey = new PdfMonkeyService();
            
            Log::info("Verificando configuración de PdfMonkey");
            // Comprobamos que tenemos las credenciales (API Key y Template ID)
            if (!$pdfMonkey->estaConfigurado()) {
                Log::error("PdfMonkey no está configurado");
                return null;
            }

            // 1. RECOGIDA DE DATOS:
            // Hacemos una consulta compleja (JOINs) para obtener toda la info del contrato en una sola fila.
            Log::info("Obteniendo datos del alquiler: {$idAlquiler}");
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
                    DB::raw($this->obtenerSelectDireccionPropiedad('p')), // Construye la dirección completa
                    'p.ciudad_propiedad',
                    'p.precio_propiedad'
                )
                ->first();

            if (!$datosAlquiler) {
                Log::error("No se encontraron datos del alquiler: {$idAlquiler}");
                return null;
            }

            Log::info("Datos del alquiler obtenidos correctamente");

            // Calculamos la fianza automáticamente (ej: 2 meses)
            $precioMensual = (float) ($datosAlquiler->precio_propiedad ?? 0);
            $fianza = $precioMensual * 2;

            // 2. CONSTRUCCIÓN DEL PAYLOAD:
            // Creamos un array asociativo donde las claves coinciden con las variables de la plantilla HTML en PdfMonkey.
            // Ej: Si en la plantilla pone {{nombre_arrendador}}, aquí usamos la clave 'nombre_arrendador'.
            $datosContrato = [
                'nombre_arrendador' => $datosAlquiler->nombre_arrendador,
                'email_arrendador' => $datosAlquiler->email_arrendador,
                'nombre_inquilino' => $datosAlquiler->nombre_inquilino,
                'email_inquilino' => $datosAlquiler->email_inquilino,
                'titulo_propiedad' => $datosAlquiler->titulo_propiedad,
                'direccion_propiedad' => $datosAlquiler->direccion_propiedad,
                'ciudad_propiedad' => $datosAlquiler->ciudad_propiedad,
                'precio_mensual' => number_format($precioMensual, 2, '.', ''), // Formato decimal estricto
                'fianza' => number_format($fianza, 2, '.', ''),
                'fecha_inicio' => Carbon::parse($datosAlquiler->fecha_inicio_alquiler)->format('d/m/Y'),
                'fecha_fin' => $datosAlquiler->fecha_fin_alquiler 
                    ? Carbon::parse($datosAlquiler->fecha_fin_alquiler)->format('d/m/Y')
                    : 'Indefinida',
                'fecha_generacion' => Carbon::now()->format('d/m/Y'),
            ];

            Log::info("Enviando solicitud a PdfMonkey para alquiler: {$idAlquiler}");

            // 3. PETICIÓN A LA API:
            // Enviamos los datos a PdfMonkey de forma SÍNCRONA (esperamos a que termine de generar).
            // También le pasamos un nombre de archivo personalizado en el 'meta'.
            $respuesta = $pdfMonkey->crearDocumentoSincronizado(
                $datosContrato,
                $pdfMonkey->construirMeta([], 'contrato_' . $idAlquiler . '.pdf')
            ); 

            Log::info("Respuesta de PdfMonkey recibida", ['respuesta' => $respuesta]);

            // 4. PROCESAMIENTO DE RESPUESTA:
            // Extraemos la URL de descarga del JSON que nos devuelve la API.
            
            // Opción A: La API devuelve la URL directamente en 'document_card'
            if (isset($respuesta['document_card']['download_url'])) {
                
                $urlDescarga = $respuesta['document_card']['download_url'];
                Log::info("URL de descarga obtenida desde document_card: {$urlDescarga}");
                return $urlDescarga;
            }

            // Opción B: La API devuelve solo el ID del documento y tenemos que pedir la URL aparte
            if (isset($respuesta['document']) && isset($respuesta['document']['id'])) {
                $idDocumentoPdf = $respuesta['document']['id'];
                $urlDescarga = $pdfMonkey->obtenerUrlDescarga($idDocumentoPdf);
                
                Log::info("URL de descarga obtenida: {$urlDescarga}");
                return $urlDescarga;
            }

            // Si la respuesta no tiene el formato esperado
            Log::error("Respuesta inválida de PdfMonkey", ['respuesta' => $respuesta]);
            return null;
        } catch (\Exception $e) {
            // Captura cualquier error de red o de la API
            Log::error('Error al generar PDF on-demand: ' . $e->getMessage(), [
                'exception' => $e,
                'alquiler_id' => $idAlquiler
            ]);
            return null;
        }
    }

    private function contarFirmados(int $arrendadorId, array $columnas): int
    {
        $consulta = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId);

        if ($columnas['estado']) {
            return (clone $consulta)
                ->where("c.{$columnas['estado']}", 'firmado')
                ->count('c.id_contrato');
        }

        if ($columnas['firmado_arrendador'] && $columnas['firmado_inquilino']) {
            return (clone $consulta)
                ->where("c.{$columnas['firmado_arrendador']}", true)
                ->where("c.{$columnas['firmado_inquilino']}", true)
                ->count('c.id_contrato');
        }

        return 0;
    }

    private function seleccionarColumnaContrato(?string $columna, string $alias, string $valorDefecto): string
    {
        if ($columna) {
            return "c.{$columna} as {$alias}";
        }

        return "{$valorDefecto} as {$alias}";
    }

    private function obtenerColumnasContrato(): array
    {
        return [
            'url_pdf' => $this->resolverColumnaContrato('url_pdf_contrato', 'url_contrato'),
            'firmado_arrendador' => $this->resolverColumnaContrato('firmado_arrendador', 'firmado_arrendador_contrato'),
            'fecha_firma_arrendador' => $this->resolverColumnaContrato('fecha_firma_arrendador', 'fecha_firma_arrendador_contrato'),
            'ip_firma_arrendador' => $this->resolverColumnaContrato('ip_firma_arrendador', 'ip_firma_arrendador_contrato'),
            'firmado_inquilino' => $this->resolverColumnaContrato('firmado_inquilino', 'firmado_inquilino_contrato'),
            'fecha_firma_inquilino' => $this->resolverColumnaContrato('fecha_firma_inquilino', 'fecha_firma_inquilino_contrato'),
            'estado' => $this->resolverColumnaContrato('estado_contrato', 'estado_contrato'),
            'actualizado' => $this->resolverColumnaContrato('actualizado_contrato', 'actualizado_contrato'),
        ];
    }

    private function resolverColumnaContrato(string $primaria, string $alterna): ?string
    {
        if (Schema::hasColumn('tbl_contrato', $primaria)) {
            return $primaria;
        }

        if (Schema::hasColumn('tbl_contrato', $alterna)) {
            return $alterna;
        }

        return null;
    }

    private function obtenerSelectDireccionPropiedad(string $aliasTabla = 'p'): string
    {
        if (Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
            return "{$aliasTabla}.direccion_propiedad as direccion_propiedad";
        }

        $partes = [];
        foreach (['calle_propiedad', 'numero_propiedad', 'piso_propiedad', 'puerta_propiedad'] as $columna) {
            if (Schema::hasColumn('tbl_propiedad', $columna)) {
                $partes[] = "NULLIF(TRIM({$aliasTabla}.{$columna}), '')";
            }
        }

        if (empty($partes)) {
            return "'' as direccion_propiedad";
        }

        return 'TRIM(CONCAT_WS(\' \' , ' . implode(', ', $partes) . ')) as direccion_propiedad';
    }

    private function obtenerIdArrendador(Request $request): int
    {
        $arrendadorId = (int) $request->query('arrendador_id', $request->input('arrendador_id', 0));

        if ($arrendadorId > 0) {
            return $arrendadorId;
        }

        return (int) DB::table('tbl_usuario as u')
            ->join('tbl_propiedad as p', 'p.id_arrendador_fk', '=', 'u.id_usuario')
            ->where('u.activo_usuario', true)
            ->groupBy('u.id_usuario')
            ->select('u.id_usuario', DB::raw('COUNT(*) as total_propiedades'))
            ->orderByDesc('total_propiedades')
            ->orderBy('u.id_usuario')
            ->value('u.id_usuario');
    }

    private function obtenerInicialAvatar(?string $nombre): string 
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
