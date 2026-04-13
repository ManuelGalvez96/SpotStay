<div
  class="export-wrapper"
  style="
    width: 100%;
    min-height: 100vh;
    position: relative;
    font-family: var(--font-family-body);
    background-color: var(--background);
  "
>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@100;200;300;400;500;600;700;800;900&family=Geist:wght@100;200;300;400;500;600;700;800;900&family=IBM+Plex+Mono:wght@100;200;300;400;500;600;700&family=IBM+Plex+Sans:wght@100;200;300;400;500;600;700&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:wght@200;300;400;500;600;700;800;900&family=PT+Serif:wght@400;700&family=Roboto+Slab:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&family=Shantell+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
  />
  <html>
    <head>
      <style id="base-styles">
        * {
          box-sizing: border-box;
        }
        html,
        body {
          margin: 0;
          padding: 0;
          width: 100%;
          overflow-x: hidden;
        }
        .export-wrapper {
          font-family: var(
            --font-family-body,
            system-ui,
            -apple-system,
            sans-serif
          );
          color: var(--foreground);
          background: var(--background);
          margin: 0;
          line-height: 1.6;
        }
        h1,
        h2,
        h3,
        h4,
        p,
        ul {
          margin: 0;
          padding: 0;
        }
        a {
          text-decoration: none;
          color: inherit;
        }
        img {
          max-width: 100%;
          height: auto;
          display: block;
        }
      </style>

      <style id="layout-styles">
        .container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 32px;
        }
        .section {
          padding: 112px 0;
        }
        .section-muted {
          background: var(--muted);
        }
        .pt-120 {
          padding-top: 120px;
        }
        .pb-120 {
          padding-bottom: 120px;
        }

        .grid {
          display: grid;
        }
        .grid-cols-2 {
          grid-template-columns: repeat(2, 1fr);
        }
        .grid-cols-3 {
          grid-template-columns: repeat(3, 1fr);
        }
        .grid-cols-4 {
          grid-template-columns: repeat(4, 1fr);
        }
        .gap-2 {
          gap: 8px;
        }
        .gap-4 {
          gap: 16px;
        }
        .gap-6 {
          gap: 24px;
        }
        .gap-8 {
          gap: 32px;
        }
        .gap-12 {
          gap: 48px;
        }
      </style>

      <style id="flex-utilities">
        .flex {
          display: flex;
        }
        .flex-col {
          flex-direction: column;
        }
        .items-center {
          align-items: center;
        }
        .justify-between {
          justify-content: space-between;
        }
        .justify-center {
          justify-content: center;
        }
        .flex-1 {
          flex: 1;
        }
      </style>

      <style id="typography">
        .text-center {
          text-align: center;
        }
        .text-6xl {
          font-size: 64px;
          font-weight: 700;
          line-height: 1.1;
          letter-spacing: -0.02em;
        }
        .text-5xl {
          font-size: 56px;
          font-weight: 700;
          line-height: 1.1;
          letter-spacing: -0.02em;
        }
        .text-4xl {
          font-size: 40px;
          font-weight: 700;
          line-height: 1.2;
          letter-spacing: -0.02em;
        }
        .text-3xl {
          font-size: 32px;
          font-weight: 700;
          line-height: 1.2;
          letter-spacing: -0.01em;
        }
        .text-2xl {
          font-size: 24px;
          font-weight: 600;
        }
        .text-xl {
          font-size: 20px;
          font-weight: 600;
        }
        .text-lg {
          font-size: 18px;
          color: var(--muted-foreground);
        }
        .text-sm {
          font-size: 14px;
        }
        .font-medium {
          font-weight: 500;
        }
        .text-muted {
          color: var(--muted-foreground);
        }
        .text-primary {
          color: var(--primary);
        }
        .text-accent {
          color: var(--accent);
        }
      </style>

      <style id="components">
        .btn {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          height: 44px;
          padding: 0 24px;
          border-radius: var(--radius-md);
          font-weight: 500;
          font-size: 15px;
          cursor: pointer;
          white-space: nowrap;
        }
        .btn-lg {
          height: 52px;
          padding: 0 32px;
          font-size: 16px;
        }
        .btn-primary {
          background: var(--primary);
          color: var(--primary-foreground);
        }
        .btn-outline {
          border: 1px solid var(--border);
          background: var(--background);
          color: var(--foreground);
        }
        .btn-ghost {
          background: transparent;
          color: var(--foreground);
        }
        .btn-accent {
          background: var(--accent);
          color: var(--accent-foreground);
        }

        .card {
          border-radius: var(--radius-lg);
          border: 1px solid var(--border);
          background: var(--card);
          overflow: hidden;
        }
        .card-body {
          padding: 24px;
        }

        .navbar {
          height: 80px;
          border-bottom: 1px solid var(--border);
          position: sticky;
          top: 0;
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(8px);
          z-index: 50;
        }
        .nav-link {
          font-size: 15px;
          font-weight: 500;
          color: var(--foreground);
        }

        .search-bar {
          background: var(--card);
          border-radius: var(--radius-xl);
          padding: 12px 12px 12px 32px;
          display: flex;
          align-items: center;
          box-shadow: 0 20px 40px -10px rgba(15, 27, 45, 0.1);
          border: 1px solid var(--border);
          gap: 24px;
        }
        .search-input-group {
          display: flex;
          flex-direction: column;
          gap: 4px;
        }
        .search-label {
          font-size: 12px;
          font-weight: 600;
          color: var(--foreground);
          text-transform: uppercase;
          letter-spacing: 0.05em;
        }
        .search-value {
          font-size: 15px;
          color: var(--muted-foreground);
        }
        .search-divider {
          width: 1px;
          height: 40px;
          background: var(--border);
        }
      </style>
    </head>
    <body>
      <nav class="navbar" id="top-nav">
        <div
          class="container flex items-center justify-between"
          style="height: 100%"
        >
          <div class="flex items-center gap-12">
            <a
              href="#"
              class="flex items-center"
              data-media-type="banani-button"
            >
              <img
                src="https://firebasestorage.googleapis.com/v0/b/banani-prod.appspot.com/o/reference-images%2F33506986-ca37-4e67-98fe-bd56178669bd?alt=media&amp;token=1a48934b-52b6-429c-ad03-7f55dcaf5bf0"
                alt="SpotStay Logo"
                style="height: 48px"
              />
            </a>
            <div class="flex gap-8">
              <a href="#" class="nav-link" data-media-type="banani-button"
                >Buscar Propiedades</a
              >
              <a href="#" class="nav-link" data-media-type="banani-button"
                >Cómo funciona</a
              >
              <a href="#" class="nav-link" data-media-type="banani-button"
                >Soy Propietario</a
              >
            </div>
          </div>
          <div class="flex items-center gap-4">
            <a href="#" class="btn btn-ghost" data-media-type="banani-button"
              >Iniciar sesión</a
            >
            <a href="#" class="btn btn-primary" data-media-type="banani-button"
              >Regístrate</a
            >
          </div>
        </div>
      </nav>

      <section
        class="section pt-120 pb-120"
        id="hero-section"
        style="
          position: relative;
          overflow: hidden;
          background: linear-gradient(
            180deg,
            var(--secondary) 0%,
            var(--background) 72%
          );
        "
      >
        <div
          style="
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            opacity: 0.24;
          "
        >
          <img
            data-aspect-ratio="21:9"
            data-query="bright modern apartment interior living room sunny with deep blue and fresh green accents, airy premium real estate landing palette"
            style="width: 100%; height: 100%; object-fit: cover"
            src="https://storage.googleapis.com/banani-generated-images/generated-images/e1686362-841a-4366-969e-f953ecb8d1cc.jpg"
          />
        </div>
        <div class="container" style="position: relative; z-index: 1">
          <div class="text-center" style="max-width: 800px; margin: 0 auto">
            <div
              style="
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: var(--card);
                color: var(--primary);
                border: 1px solid var(--border);
                border-radius: 999px;
                padding: 8px 14px;
                margin-bottom: 24px;
                font-size: 14px;
                font-weight: 500;
                white-space: nowrap;
              "
            >
              <span
                style="
                  width: 8px;
                  height: 8px;
                  border-radius: 50%;
                  background: var(--accent);
                "
              ></span>
              Búsqueda inteligente de alquileres
            </div>
            <h1
              class="text-6xl"
              style="margin-bottom: 24px; color: var(--foreground)"
            >
              Encuentra el lugar perfecto para vivir
            </h1>
            <p
              class="text-lg"
              style="
                margin-bottom: 56px;
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
                color: var(--foreground);
                opacity: 0.78;
              "
            >
              Explora miles de propiedades en alquiler. Firma tu contrato de
              forma digital, paga online y comunícate directamente con el
              propietario sin intermediarios complicados.
            </p>

            <div class="search-bar" id="hero-search">
              <div class="search-input-group flex-1" style="text-align: left">
                <span class="search-label">Ubicación</span>
                <span class="search-value">¿Dónde quieres vivir?</span>
              </div>
              <div class="search-divider"></div>
              <div class="search-input-group flex-1" style="text-align: left">
                <span class="search-label">Tipo</span>
                <span class="search-value">Piso, Casa, Estudio...</span>
              </div>
              <div class="search-divider"></div>
              <div class="search-input-group flex-1" style="text-align: left">
                <span class="search-label">Precio Máx.</span>
                <span class="search-value">Sin límite</span>
              </div>
              <a
                href="#"
                class="btn btn-primary btn-lg"
                style="
                  border-radius: 99px;
                  width: 64px;
                  height: 64px;
                  padding: 0;
                "
                data-media-type="banani-button"
              >
                <div
                  style="
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                  "
                >
                  <iconify-icon
                    icon="lucide:search"
                    style="font-size: 24px; color: var(--primary-foreground)"
                  ></iconify-icon>
                </div>
              </a>
            </div>
          </div>
        </div>
      </section>

      <section class="section" id="featured-properties">
        <div class="container">
          <div
            class="flex items-center justify-between"
            style="margin-bottom: 48px"
          >
            <div>
              <h2 class="text-3xl" style="margin-bottom: 8px">
                Propiedades destacadas
              </h2>
              <p class="text-lg">
                Descubre los alojamientos más populares y mejor valorados.
              </p>
            </div>
            <a href="#" class="btn btn-outline" data-media-type="banani-button"
              >Ver todas las propiedades</a
            >
          </div>

          <div class="grid grid-cols-3 gap-8">
            <div
              class="card"
              data-media-type="banani-button"
              style="cursor: pointer"
            >
              <div style="position: relative">
                <img
                  data-aspect-ratio="4:3"
                  data-query="modern apartment living room interior city view with dark blue and green decor accents"
                  alt="Apartment view"
                  src="https://storage.googleapis.com/banani-generated-images/generated-images/ebd66fb3-dd60-4719-914e-3c678f2bec53.jpg"
                />
                <div
                  style="
                    position: absolute;
                    top: 16px;
                    right: 16px;
                    background: var(--card);
                    color: var(--primary);
                    padding: 4px 12px;
                    border-radius: 99px;
                    font-weight: 600;
                    font-size: 14px;
                    border: 1px solid var(--border);
                  "
                >
                  Nuevo
                </div>
              </div>
              <div class="card-body">
                <div
                  class="flex items-center justify-between"
                  style="margin-bottom: 12px"
                >
                  <span class="text-2xl" style="color: var(--primary)"
                    >$1,200 <span class="text-sm text-muted">/ mes</span></span
                  >
                  <div
                    class="flex items-center gap-1 text-sm font-medium"
                    style="color: var(--accent)"
                  >
                    <iconify-icon
                      icon="lucide:star"
                      style="color: var(--accent)"
                    ></iconify-icon>
                    4.9
                  </div>
                </div>
                <h3 class="text-xl" style="margin-bottom: 8px">
                  Apartamento céntrico con terraza
                </h3>
                <p
                  class="text-muted flex items-center gap-2"
                  style="margin-bottom: 24px; font-size: 15px"
                >
                  <iconify-icon
                    icon="lucide:map-pin"
                    style="font-size: 16px; color: var(--accent)"
                  ></iconify-icon>
                  Centro Histórico, Madrid
                </p>
                <div class="flex items-center gap-6 text-sm text-muted">
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:bed-double"
                      style="font-size: 18px"
                    ></iconify-icon>
                    2 Hab
                  </div>
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:bath"
                      style="font-size: 18px"
                    ></iconify-icon>
                    1 Baño
                  </div>
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:maximize"
                      style="font-size: 18px"
                    ></iconify-icon>
                    75 m²
                  </div>
                </div>
              </div>
            </div>

            <div
              class="card"
              data-media-type="banani-button"
              style="cursor: pointer"
            >
              <img
                data-aspect-ratio="4:3"
                data-query="cozy minimalist bedroom natural light with fresh green and deep blue styling"
                alt="Bedroom view"
                src="https://storage.googleapis.com/banani-generated-images/generated-images/54a19312-2369-4c7e-967c-874359102d60.jpg"
              />
              <div class="card-body">
                <div
                  class="flex items-center justify-between"
                  style="margin-bottom: 12px"
                >
                  <span class="text-2xl" style="color: var(--primary)"
                    >$950 <span class="text-sm text-muted">/ mes</span></span
                  >
                  <div
                    class="flex items-center gap-1 text-sm font-medium"
                    style="color: var(--accent)"
                  >
                    <iconify-icon
                      icon="lucide:star"
                      style="color: var(--accent)"
                    ></iconify-icon>
                    4.7
                  </div>
                </div>
                <h3 class="text-xl" style="margin-bottom: 8px">
                  Estudio minimalista luminoso
                </h3>
                <p
                  class="text-muted flex items-center gap-2"
                  style="margin-bottom: 24px; font-size: 15px"
                >
                  <iconify-icon
                    icon="lucide:map-pin"
                    style="font-size: 16px; color: var(--accent)"
                  ></iconify-icon>
                  Barrio Norte, Buenos Aires
                </p>
                <div class="flex items-center gap-6 text-sm text-muted">
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:bed-double"
                      style="font-size: 18px"
                    ></iconify-icon>
                    1 Hab
                  </div>
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:bath"
                      style="font-size: 18px"
                    ></iconify-icon>
                    1 Baño
                  </div>
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:maximize"
                      style="font-size: 18px"
                    ></iconify-icon>
                    45 m²
                  </div>
                </div>
              </div>
            </div>

            <div
              class="card"
              data-media-type="banani-button"
              style="cursor: pointer"
            >
              <img
                data-aspect-ratio="4:3"
                data-query="luxury modern house exterior with pool at sunset, dark blue architecture accents and lush green landscaping"
                alt="House exterior"
                src="https://storage.googleapis.com/banani-generated-images/generated-images/1f50e084-0d4c-433d-8a70-42b5c7a66d10.jpg"
              />
              <div class="card-body">
                <div
                  class="flex items-center justify-between"
                  style="margin-bottom: 12px"
                >
                  <span class="text-2xl" style="color: var(--primary)"
                    >$2,500 <span class="text-sm text-muted">/ mes</span></span
                  >
                  <div
                    class="flex items-center gap-1 text-sm font-medium"
                    style="color: var(--accent)"
                  >
                    <iconify-icon
                      icon="lucide:star"
                      style="color: var(--accent)"
                    ></iconify-icon>
                    5.0
                  </div>
                </div>
                <h3 class="text-xl" style="margin-bottom: 8px">
                  Chalet familiar con piscina
                </h3>
                <p
                  class="text-muted flex items-center gap-2"
                  style="margin-bottom: 24px; font-size: 15px"
                >
                  <iconify-icon
                    icon="lucide:map-pin"
                    style="font-size: 16px; color: var(--accent)"
                  ></iconify-icon>
                  Las Condes, Santiago
                </p>
                <div class="flex items-center gap-6 text-sm text-muted">
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:bed-double"
                      style="font-size: 18px"
                    ></iconify-icon>
                    4 Hab
                  </div>
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:bath"
                      style="font-size: 18px"
                    ></iconify-icon>
                    3 Baños
                  </div>
                  <div class="flex items-center gap-2">
                    <iconify-icon
                      icon="lucide:maximize"
                      style="font-size: 18px"
                    ></iconify-icon>
                    210 m²
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section
        class="section section-muted"
        id="map-section"
        style="
          background: linear-gradient(
            180deg,
            var(--muted) 0%,
            var(--secondary) 100%
          );
        "
      >
        <div class="container">
          <div class="grid grid-cols-2 gap-12 items-center">
            <div>
              <div
                style="
                  width: 64px;
                  height: 64px;
                  border-radius: var(--radius-md);
                  background: var(--card);
                  border: 1px solid var(--border);
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  margin-bottom: 24px;
                "
              >
                <iconify-icon
                  icon="lucide:map"
                  style="font-size: 32px; color: var(--accent)"
                ></iconify-icon>
              </div>
              <h2 class="text-4xl" style="margin-bottom: 24px">
                Busca exactamente donde quieres vivir
              </h2>
              <p class="text-lg" style="margin-bottom: 32px">
                Nuestro mapa interactivo te permite explorar vecindarios, ver la
                proximidad a transporte público, escuelas y comercios. Encuentra
                la ubicación ideal sin dar vueltas innecesarias.
              </p>
              <ul
                class="flex flex-col gap-4"
                style="list-style: none; margin-bottom: 40px"
              >
                <li class="flex items-center gap-3 text-lg">
                  <div
                    style="
                      width: 24px;
                      height: 24px;
                      border-radius: 50%;
                      background: var(--accent);
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      color: var(--accent-foreground);
                    "
                  >
                    <iconify-icon
                      icon="lucide:check"
                      style="font-size: 14px"
                    ></iconify-icon>
                  </div>
                  Filtra por precio, habitaciones y comodidades en tiempo real.
                </li>
                <li class="flex items-center gap-3 text-lg">
                  <div
                    style="
                      width: 24px;
                      height: 24px;
                      border-radius: 50%;
                      background: var(--accent);
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      color: var(--accent-foreground);
                    "
                  >
                    <iconify-icon
                      icon="lucide:check"
                      style="font-size: 14px"
                    ></iconify-icon>
                  </div>
                  Guarda tus búsquedas y recibe alertas de nuevas propiedades.
                </li>
              </ul>
              <a
                href="#"
                class="btn btn-primary btn-lg"
                data-media-type="banani-button"
                >Abrir mapa interactivo</a
              >
            </div>
            <div
              style="
                border-radius: var(--radius-xl);
                overflow: hidden;
                border: 4px solid var(--card);
                box-shadow: 0 25px 50px -12px rgba(15, 27, 45, 0.12);
              "
            >
              <img
                data-aspect-ratio="4:3"
                data-query="interactive real estate map interface with property pins, modern UI in dark blue and green palette"
                alt="Mapa interactivo"
                src="https://storage.googleapis.com/banani-generated-images/generated-images/b3f5f71f-50fc-471c-a951-e1b457534aab.jpg"
              />
            </div>
          </div>
        </div>
      </section>

      <section class="section" id="benefits-section">
        <div class="container">
          <div
            class="text-center"
            style="max-width: 700px; margin: 0 auto 64px"
          >
            <h2 class="text-4xl" style="margin-bottom: 16px">
              Una experiencia de alquiler sin fricciones
            </h2>
            <p class="text-lg">
              SpotStay digitaliza todo el proceso para que alquilar sea seguro,
              rápido y transparente.
            </p>
          </div>

          <div class="grid grid-cols-3 gap-8">
            <div
              class="flex flex-col items-center text-center"
              style="padding: 32px 24px"
            >
              <div
                style="
                  width: 80px;
                  height: 80px;
                  border-radius: 50%;
                  background: var(--secondary);
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  margin-bottom: 24px;
                "
              >
                <iconify-icon
                  icon="lucide:file-signature"
                  style="font-size: 36px; color: var(--primary)"
                ></iconify-icon>
              </div>
              <h3 class="text-2xl" style="margin-bottom: 16px">
                Contratos 100% Digitales
              </h3>
              <p class="text-muted text-lg">
                Firma tu contrato de alquiler desde cualquier dispositivo con
                validez legal completa. Descarga una copia en PDF cuando lo
                necesites.
              </p>
            </div>

            <div
              class="flex flex-col items-center text-center"
              style="padding: 32px 24px"
            >
              <div
                style="
                  width: 80px;
                  height: 80px;
                  border-radius: 50%;
                  background: var(--secondary);
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  margin-bottom: 24px;
                "
              >
                <iconify-icon
                  icon="lucide:credit-card"
                  style="font-size: 36px; color: var(--accent)"
                ></iconify-icon>
              </div>
              <h3 class="text-2xl" style="margin-bottom: 16px">
                Pagos Seguros y Online
              </h3>
              <p class="text-muted text-lg">
                Olvídate de las transferencias manuales. Paga tu alquiler a
                través de nuestra plataforma segura y revisa tu historial de
                pagos al instante.
              </p>
            </div>

            <div
              class="flex flex-col items-center text-center"
              style="padding: 32px 24px"
            >
              <div
                style="
                  width: 80px;
                  height: 80px;
                  border-radius: 50%;
                  background: var(--secondary);
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  margin-bottom: 24px;
                "
              >
                <iconify-icon
                  icon="lucide:message-circle"
                  style="font-size: 36px; color: var(--primary)"
                ></iconify-icon>
              </div>
              <h3 class="text-2xl" style="margin-bottom: 16px">
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

      <section class="section" id="owner-cta" style="padding-top: 0">
        <div class="container">
          <div
            style="
              background: var(--foreground);
              color: var(--background);
              border-radius: var(--radius-xl);
              padding: 80px 64px;
              display: flex;
              align-items: center;
              justify-content: space-between;
            "
          >
            <div style="max-width: 600px">
              <div
                style="
                  display: inline-flex;
                  align-items: center;
                  gap: 8px;
                  background: rgba(255, 255, 255, 0.08);
                  color: var(--accent);
                  border-radius: 999px;
                  padding: 8px 14px;
                  margin-bottom: 20px;
                  font-size: 14px;
                  font-weight: 500;
                  white-space: nowrap;
                "
              >
                También para propietarios
              </div>
              <h2
                class="text-4xl"
                style="margin-bottom: 24px; color: var(--primary-foreground)"
              >
                ¿Eres propietario o gestor inmobiliario?
              </h2>
              <p
                class="text-lg"
                style="margin-bottom: 32px; color: rgba(255, 255, 255, 0.8)"
              >
                SpotStay también es para ti. Publica tus propiedades, evalúa
                inquilinos, cobra alquileres automáticamente y gestiona
                mantenimientos desde un único panel de control centralizado.
              </p>
              <a
                href="#"
                class="btn btn-accent btn-lg"
                data-media-type="banani-button"
                >Descubrir portal de propietarios</a
              >
            </div>
            <div
              style="
                width: 300px;
                height: 300px;
                display: flex;
                align-items: center;
                justify-content: center;
              "
            >
              <iconify-icon
                icon="lucide:building-2"
                style="font-size: 200px; color: rgba(255, 255, 255, 0.12)"
              ></iconify-icon>
            </div>
          </div>
        </div>
      </section>

      <footer
        style="
          border-top: 1px solid var(--border);
          padding: 64px 0 32px;
          background: var(--background);
        "
        id="page-footer"
      >
        <div class="container">
          <div class="grid grid-cols-4 gap-12" style="margin-bottom: 64px">
            <div style="grid-column: span 2">
              <img
                src="https://firebasestorage.googleapis.com/v0/b/banani-prod.appspot.com/o/reference-images%2F33506986-ca37-4e67-98fe-bd56178669bd?alt=media&amp;token=1a48934b-52b6-429c-ad03-7f55dcaf5bf0"
                alt="SpotStay Logo"
                style="height: 48px; margin-bottom: 24px"
              />
              <p class="text-muted" style="max-width: 300px">
                La plataforma integral que simplifica el alquiler para
                inquilinos, propietarios y gestores.
              </p>
            </div>
            <div>
              <h4 class="font-medium" style="margin-bottom: 24px">
                Para Inquilinos
              </h4>
              <ul
                class="flex flex-col gap-3 text-muted"
                style="list-style: none"
              >
                <li>
                  <a href="#" data-media-type="banani-button"
                    >Buscar propiedades</a
                  >
                </li>
                <li>
                  <a href="#" data-media-type="banani-button">Cómo funciona</a>
                </li>
                <li>
                  <a href="#" data-media-type="banani-button"
                    >Preguntas frecuentes</a
                  >
                </li>
                <li><a href="#" data-media-type="banani-button">Soporte</a></li>
              </ul>
            </div>
            <div>
              <h4 class="font-medium" style="margin-bottom: 24px">
                Para Propietarios
              </h4>
              <ul
                class="flex flex-col gap-3 text-muted"
                style="list-style: none"
              >
                <li>
                  <a href="#" data-media-type="banani-button"
                    >Publicar propiedad</a
                  >
                </li>
                <li>
                  <a href="#" data-media-type="banani-button"
                    >Gestión de pagos</a
                  >
                </li>
                <li>
                  <a href="#" data-media-type="banani-button"
                    >Contratos digitales</a
                  >
                </li>
                <li>
                  <a href="#" data-media-type="banani-button"
                    >Planes y precios</a
                  >
                </li>
              </ul>
            </div>
          </div>
          <div
            class="flex items-center justify-between"
            style="padding-top: 32px; border-top: 1px solid var(--border)"
          >
            <p class="text-sm text-muted">
              © 2025 SpotStay. Todos los derechos reservados.
            </p>
            <div class="flex gap-6 text-sm text-muted">
              <a href="#" data-media-type="banani-button"
                >Términos de servicio</a
              >
              <a href="#" data-media-type="banani-button"
                >Política de Privacidad</a
              >
              <a href="#" data-media-type="banani-button">Aviso Legal</a>
            </div>
          </div>
        </div>
      </footer>
    </body>
  </html>
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    :root {
      --background: #f7f8fa;
      --foreground: #0f1b2d;
      --border: #dce5ec;
      --input: #ffffff;
      --primary: #123b7a;
      --primary-foreground: #ffffff;
      --secondary: #eaf6f3;
      --secondary-foreground: #0f1b2d;
      --muted: #f1f5f7;
      --muted-foreground: #667085;
      --success: #19a974;
      --success-foreground: #ffffff;
      --accent: #19a974;
      --accent-foreground: #ffffff;
      --destructive: #ffecef;
      --destructive-foreground: #7a0710;
      --warning: #fff4e6;
      --warning-foreground: #7a4b00;
      --card: #ffffff;
      --card-foreground: #0f1b2d;
      --sidebar: #f4f8fb;
      --sidebar-foreground: #0f1b2d;
      --sidebar-primary: #123b7a;
      --sidebar-primary-foreground: #ffffff;
      --radius-sm: 4px;
      --radius-md: 6px;
      --radius-lg: 8px;
      --radius-xl: 12px;
      --font-family-body: Inter;
    }
  </style>
</div>
