<div class="envoltorio-principal">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@100;200;300;400;500;600;700;800;900&family=Geist:wght@100;200;300;400;500;600;700;800;900&family=IBM+Plex+Mono:wght@100;200;300;400;500;600;700&family=IBM+Plex+Sans:wght@100;200;300;400;500;600;700&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:wght@200;300;400;500;600;700;800;900&family=PT+Serif:wght@400;700&family=Roboto+Slab:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&family=Shantell+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />
  <html>

  <head>
    <link rel="stylesheet" href="{{ asset('css/inicio/styles.css') }}">
  </head>

  <body>
    <nav class="navbar" id="top-nav">
      <div
        class="container flex items-center justify-between nav-contenedor-altura">
        <div class="flex items-center gap-12">
          <a
            href="#"
            class="flex items-center"
            data-media-type="banani-button">
            <img
              class="logo-imagen"
              src="https://firebasestorage.googleapis.com/v0/b/banani-prod.appspot.com/o/reference-images%2F33506986-ca37-4e67-98fe-bd56178669bd?alt=media&amp;token=1a48934b-52b6-429c-ad03-7f55dcaf5bf0"
              alt="Logotipo de SpotStay" />
          </a>
          <div class="flex gap-8">
            <a href="#" class="nav-link" data-media-type="banani-button">Buscar Propiedades</a>
            <a href="#" class="nav-link" data-media-type="banani-button">Cómo funciona</a>
            <a href="#" class="nav-link" data-media-type="banani-button">Soy Propietario</a>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <a href="{{ route('login') }}" class="btn btn-ghost" data-media-type="banani-button">Iniciar sesión</a>
          <a href="#" class="btn btn-primary" data-media-type="banani-button">Regístrate</a>
        </div>
      </div>
    </nav>

    <section
      class="section pt-120 pb-120 seccion-hero"
      id="hero-section">
      <div class="hero-superposicion-fondo">
        <img
          class="hero-imagen-ajuste"
          data-aspect-ratio="21:9"
          data-query="interior de apartamento moderno y luminoso sala de estar soleada con acentos de azul profundo y verde fresco, paleta de aterrizaje inmobiliaria premium y aireada"
          src="https://storage.googleapis.com/banani-generated-images/generated-images/e1686362-841a-4366-969e-f953ecb8d1cc.jpg" />
      </div>
      <div class="container capa-contenido">
        <div class="text-center contenedor-max-centrado">
          <div class="insignia-inteligente">
            <span class="punto-indicador-acento"></span>
            Búsqueda inteligente de alquileres
          </div>
          <h1
            class="text-6xl hero-titulo">
            Encuentra el lugar perfecto para vivir
          </h1>
          <p
            class="text-lg hero-descripcion">
            Explora miles de propiedades en alquiler. Firma tu contrato de
            forma digital, paga online y comunícate directamente con el
            propietario sin intermediarios complicados.
          </p>

          <div class="search-bar" id="hero-search">
            <div class="search-input-group flex-1 alineado-izquierda">
              <span class="search-label">Ubicación</span>
              <span class="search-value">¿Dónde quieres vivir?</span>
            </div>
            <div class="search-divider"></div>
            <div class="search-input-group flex-1 alineado-izquierda">
              <span class="search-label">Tipo</span>
              <span class="search-value">Piso, Casa, Estudio...</span>
            </div>
            <div class="search-divider"></div>
            <div class="search-input-group flex-1 alineado-izquierda">
              <span class="search-label">Precio Máx.</span>
              <span class="search-value">Sin límite</span>
            </div>
            <a
              href="#"
              class="btn btn-primary btn-lg boton-busqueda-redondo"
              data-media-type="banani-button">
              <div class="contenedor-icono-centrado">
                <iconify-icon
                  icon="lucide:search"
                  class="icono-busqueda-principal"></iconify-icon>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="featured-properties">
      <div class="container">
        <div
          class="flex items-center justify-between cabecera-seccion-propiedades">
          <div>
            <h2 class="text-3xl titulo-seccion-secundario">
              Propiedades destacadas
            </h2>
            <p class="text-lg">
              Descubre los alojamientos más populares y mejor valorados.
            </p>
          </div>
          <a href="#" class="btn btn-outline" data-media-type="banani-button">Ver todas las propiedades</a>
        </div>

        <div class="grid grid-cols-3 gap-8">
          <div
            class="card"
            data-media-type="banani-button"
            style="cursor: pointer">
            <div class="contenedor-imagen-relativo">
              <img
                data-aspect-ratio="4:3"
                data-query="vista de la ciudad desde el interior de la sala de un apartamento moderno con acentos decorativos en azul oscuro y verde"
                alt="Apartment view"
                src="https://storage.googleapis.com/banani-generated-images/generated-images/ebd66fb3-dd60-4719-914e-3c678f2bec53.jpg" />
              <div class="etiqueta-novedad">
                Nuevo
              </div>
            </div>
            <div class="card-body">
              <div
                class="flex items-center justify-between margen-inf-12">
                <span class="text-2xl color-primario">$1,200 <span class="text-sm text-muted">/ mes</span></span>
                <div
                  class="flex items-center gap-1 text-sm font-medium color-acento">
                  <iconify-icon
                    icon="lucide:star"
                    class="color-acento"></iconify-icon>
                  4.9
                </div>
              </div>
              <h3 class="text-xl margen-inf-medio">
                Apartamento céntrico con terraza
              </h3>
              <p
                class="text-muted flex items-center gap-2 info-ubicacion-tarjeta">
                <iconify-icon
                  icon="lucide:map-pin"
                  class="icono-16-acento"></iconify-icon>
                Centro Histórico, Madrid
              </p>
              <div class="flex items-center gap-6 text-sm text-muted">
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:bed-double"
                    class="icono-18-caracteristica"></iconify-icon>
                  2 Hab
                </div>
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:bath"
                    class="icono-18-caracteristica"></iconify-icon>
                  1 Baño
                </div>
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:maximize"
                    class="icono-18-caracteristica"></iconify-icon>
                  75 m²
                </div>
              </div>
            </div>
          </div>

          <div
            class="card"
            data-media-type="banani-button"
            style="cursor: pointer">
            <img
              data-aspect-ratio="4:3"
              data-query="dormitorio minimalista acogedor con luz natural y estilo en verde fresco y azul profundo"
              alt="Bedroom view"
              src="https://storage.googleapis.com/banani-generated-images/generated-images/54a19312-2369-4c7e-967c-874359102d60.jpg" />
            <div class="card-body">
              <div
                class="flex items-center justify-between margen-inf-12">
                <span class="text-2xl color-primario">$950 <span class="text-sm text-muted">/ mes</span></span>
                <div
                  class="flex items-center gap-1 text-sm font-medium color-acento">
                  <iconify-icon
                    icon="lucide:star"
                    class="color-acento"></iconify-icon>
                  4.7
                </div>
              </div>
              <h3 class="text-xl margen-inf-medio">
                Estudio minimalista luminoso
              </h3>
              <p
                class="text-muted flex items-center gap-2 info-ubicacion-tarjeta">
                <iconify-icon
                  icon="lucide:map-pin"
                  class="icono-16-acento"></iconify-icon>
                Barrio Norte, Buenos Aires
              </p>
              <div class="flex items-center gap-6 text-sm text-muted">
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:bed-double"
                    class="icono-18-caracteristica"></iconify-icon>
                  1 Hab
                </div>
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:bath"
                    class="icono-18-caracteristica"></iconify-icon>
                  1 Baño
                </div>
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:maximize"
                    class="icono-18-caracteristica"></iconify-icon>
                  45 m²
                </div>
              </div>
            </div>
          </div>

          <div
            class="card"
            data-media-type="banani-button"
            style="cursor: pointer">
            <img
              data-aspect-ratio="4:3"
              data-query="exterior de casa moderna de lujo con piscina al atardecer, acentos arquitectónicos en azul oscuro y paisajismo verde exuberante"
              alt="House exterior"
              src="https://storage.googleapis.com/banani-generated-images/generated-images/1f50e084-0d4c-433d-8a70-42b5c7a66d10.jpg" />
            <div class="card-body">
              <div
                class="flex items-center justify-between margen-inf-12">
                <span class="text-2xl color-primario">$2,500 <span class="text-sm text-muted">/ mes</span></span>
                <div
                  class="flex items-center gap-1 text-sm font-medium color-acento">
                  <iconify-icon
                    icon="lucide:star"
                    class="color-acento"></iconify-icon>
                  5.0
                </div>
              </div>
              <h3 class="text-xl margen-inf-medio">
                Chalet familiar con piscina
              </h3>
              <p
                class="text-muted flex items-center gap-2 info-ubicacion-tarjeta">
                <iconify-icon
                  icon="lucide:map-pin"
                  class="icono-16-acento"></iconify-icon>
                Las Condes, Santiago
              </p>
              <div class="flex items-center gap-6 text-sm text-muted">
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:bed-double"
                    class="icono-18-caracteristica"></iconify-icon>
                  4 Hab
                </div>
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:bath"
                    class="icono-18-caracteristica"></iconify-icon>
                  3 Baños
                </div>
                <div class="flex items-center gap-2">
                  <iconify-icon
                    icon="lucide:maximize"
                    class="icono-18-caracteristica"></iconify-icon>
                  210 m²
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="section section-muted seccion-gradiente-mapa"
      id="map-section">
      <div class="container">
        <div class="grid grid-cols-2 gap-12 items-center">
          <div>
            <div class="contenedor-icono-mapa">
              <iconify-icon
                icon="lucide:map"
                class="icono-grande-acento"></iconify-icon>
            </div>
            <h2 class="text-4xl titulo-seccion-secundario">
              Busca exactamente donde quieres vivir
            </h2>
            <p class="text-lg margen-inf-grande">
              Nuestro mapa interactivo te permite explorar vecindarios, ver la
              proximidad a transporte público, escuelas y comercios. Encuentra
              la ubicación ideal sin dar vueltas innecesarias.
            </p>
            <ul class="flex flex-col gap-4 lista-ventajas-espaciada">
              <li class="flex items-center gap-3 text-lg">
                <div class="punto-verificacion-lista">
                  <iconify-icon
                    icon="lucide:check"
                    class="icono-busqueda-peque"></iconify-icon>
                </div>
                Filtra por precio, habitaciones y comodidades en tiempo real.
              </li>
              <li class="flex items-center gap-3 text-lg">
                <div class="punto-verificacion-lista">
                  <iconify-icon
                    icon="lucide:check"
                    style="font-size: 14px"></iconify-icon>
                </div>
                Guarda tus búsquedas y recibe alertas de nuevas propiedades.
              </li>
            </ul>
            <a
              href="#"
              class="btn btn-primary btn-lg"
              data-media-type="banani-button">Abrir mapa interactivo</a>
          </div>
          <div class="contenedor-mapa-marco">
            <img
              data-aspect-ratio="4:3"
              data-query="interfaz de mapa interactivo de bienes raíces con pines de propiedad, interfaz de usuario moderna en paleta azul oscuro y verde"
              alt="Mapa interactivo"
              src="https://storage.googleapis.com/banani-generated-images/generated-images/b3f5f71f-50fc-471c-a951-e1b457534aab.jpg" />
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="benefits-section">
      <div class="container">
        <div
          class="text-center cabecera-beneficios-intro">
          <h2 class="text-4xl margen-inf-medio">
            Una experiencia de alquiler sin fricciones
          </h2>
          <p class="text-lg">
            SpotStay digitaliza todo el proceso para que alquilar sea seguro,
            rápido y transparente.
          </p>
        </div>

        <div class="grid grid-cols-3 gap-8">
          <div
            class="flex flex-col items-center text-center tarjeta-beneficio-padding">
            <div class="circulo-beneficio-fondo">
              <iconify-icon
                icon="lucide:file-signature"
                class="icono-beneficio-grande icono-estilo-primario"></iconify-icon>
            </div>
            <h3 class="text-2xl margen-inf-medio">
              Contratos 100% Digitales
            </h3>
            <p class="text-muted text-lg">
              Firma tu contrato de alquiler desde cualquier dispositivo con
              validez legal completa. Descarga una copia en PDF cuando lo
              necesites.
            </p>
          </div>

          <div
            class="flex flex-col items-center text-center tarjeta-beneficio-padding">
            <div class="circulo-beneficio-fondo">
              <iconify-icon
                icon="lucide:credit-card"
                class="icono-beneficio-grande icono-estilo-acento"></iconify-icon>
            </div>
            <h3 class="text-2xl margen-inf-medio">
              Pagos Seguros y Online
            </h3>
            <p class="text-muted text-lg">
              Olvídate de las transferencias manuales. Paga tu alquiler a
              través de nuestra plataforma segura y revisa tu historial de
              pagos al instante.
            </p>
          </div>

          <div
            class="flex flex-col items-center text-center tarjeta-beneficio-padding">
            <div class="circulo-beneficio-fondo">
              <iconify-icon
                icon="lucide:message-circle"
                class="icono-beneficio-grande icono-estilo-primario"></iconify-icon>
            </div>
            <h3 class="text-2xl margen-inf-medio">
              Chat Directo e Incidencias
            </h3>
            <p class="text-muted text-lg">
              ¿Se ha roto algo? Repórtalo fácilmente con fotos y comunícate
              con el propietario o gestor en tiempo real a través de nuestro
              chat integrado.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="section sin-padding-superior" id="owner-cta">
      <div class="container">
        <div
          class="cta-banner-propietarios">
          <div class="columna-info-ancho">
            <div
              class="insignia-translucida">
              También para propietarios
            </div>
            <h2
              class="text-4xl texto-primario-propietarios">
              ¿Eres propietario o gestor inmobiliario?
            </h2>
            <p
              class="text-lg texto-secundario-propietarios">
              SpotStay también es para ti. Publica tus propiedades, evalúa
              inquilinos, cobra alquileres automáticamente y gestiona
              mantenimientos desde un único panel de control centralizado.
            </p>
            <a
              href="#"
              class="btn btn-accent btn-lg"
              data-media-type="banani-button">Descubrir portal de propietarios</a>
          </div>
          <div class="contenedor-decorativo-propietario">
            <iconify-icon
              icon="lucide:building-2"
              class="icono-marca-agua-gigante"></iconify-icon>
          </div>
        </div>
      </div>
    </section>

    <footer
      class="pie-de-pagina-principal"
      id="page-footer">
      <div class="container">
        <div class="grid grid-cols-4 gap-12 cabecera-seccion-propiedades">
          <div class="columna-doble">
            <img
              class="logo-footer-margen"
              src="https://firebasestorage.googleapis.com/v0/b/banani-prod.appspot.com/o/reference-images%2F33506986-ca37-4e67-98fe-bd56178669bd?alt=media&amp;token=1a48934b-52b6-429c-ad03-7f55dcaf5bf0"
              alt="SpotStay Logo" />
            <p class="text-muted texto-footer-ancho">
              La plataforma integral que simplifica el alquiler para
              inquilinos, propietarios y gestores.
            </p>
          </div>
          <div>
            <h4 class="font-medium margen-inf-grande">
              Para Inquilinos
            </h4>
            <ul
              class="flex flex-col gap-3 text-muted"
              style="list-style: none">
              <li>
                <a href="#" data-media-type="banani-button">Buscar propiedades</a>
              </li>
              <li>
                <a href="#" data-media-type="banani-button">Cómo funciona</a>
              </li>
              <li>
                <a href="#" data-media-type="banani-button">Preguntas frecuentes</a>
              </li>
              <li><a href="#" data-media-type="banani-button">Soporte</a></li>
            </ul>
          </div>
          <div>
            <h4 class="font-medium margen-inf-grande">
              Para Propietarios
            </h4>
            <ul
              class="flex flex-col gap-3 text-muted"
              style="list-style: none">
              <li>
                <a href="#" data-media-type="banani-button">Publicar propiedad</a>
              </li>
              <li>
                <a href="#" data-media-type="banani-button">Gestión de pagos</a>
              </li>
              <li>
                <a href="#" data-media-type="banani-button">Contratos digitales</a>
              </li>
              <li>
                <a href="#" data-media-type="banani-button">Planes y precios</a>
              </li>
            </ul>
          </div>
        </div>
        <div
          class="flex items-center justify-between barra-inferior-pie">
          <p class="text-sm text-muted">
            © 2025 SpotStay. Todos los derechos reservados.
          </p>
          <div class="flex gap-6 text-sm text-muted">
            <a href="#" data-media-type="banani-button">Términos de servicio</a>
            <a href="#" data-media-type="banani-button">Política de Privacidad</a>
            <a href="#" data-media-type="banani-button">Aviso Legal</a>
          </div>
        </div>
      </div>
    </footer>
  </body>

  </html>
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
</div>