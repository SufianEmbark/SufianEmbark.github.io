<main class="main-content">

    <!-- Sección principal de bienvenida con título y eslogan -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Bienvenido al IES Miguel Servet de Zaragoza</h1>
            <p class="hero-subtitle">Comprometidos con la excelencia educativa desde 1845.</p>
        </div>
    </section>

    <?php
    // Se obtiene la descripción del centro desde la configuración.
    // Si no está definida, se muestra una descripción por defecto.
    $descripcion_centro = get_config('descripcion_centro') ?: 'El IES Miguel Servet es un centro público de Educación Secundaria y Bachillerato ubicado en el Paseo Ruiseñores, 49-51, en Zaragoza. Con una larga trayectoria, ofrecemos una educación integral y de calidad, fomentando el desarrollo personal y académico de nuestro alumnado.';
    ?>

    <!-- Sección informativa "Sobre Nosotros" con la descripción del centro -->
    <section class="seccion" id="sobre-nosotros">
        <div class="seccion-contenido">
            <h2 class="seccion-titulo">Sobre Nosotros</h2>
            <!-- Muestra la descripción escapando caracteres especiales y manteniendo saltos de línea -->
            <p class="seccion-texto">
                <?= nl2br(htmlspecialchars($descripcion_centro)) ?>
            </p>
        </div>
    </section>

    <!-- Sección con los tipos de formación que ofrece el centro -->
    <section class="seccion" id="oferta-educativa">
        <div class="seccion-contenido">
            <h2 class="seccion-titulo">Oferta Educativa</h2>
            <ul class="lista-oferta">
                <li>Educación Secundaria Obligatoria (ESO)</li>
                <li>Bachillerato en las modalidades de Ciencias, Humanidades y Ciencias Sociales</li>
                <li>Programas bilingües en inglés</li>
                <li>Participación en proyectos internacionales como Erasmus+</li>
            </ul>
        </div>
    </section>

    <!-- Sección de galería de imágenes del instituto -->
    <section class="seccion" id="instalaciones">
        <div class="seccion-contenido">
            <h2 class="seccion-titulo">Galería</h2>
            <div class="gallery">
                <img src="assets/images/IES-foto-1.jpg" alt="Fachada del IES Miguel Servet">
                <img src="assets/images/IES-foto-2.jpg" alt="Instalaciones interiores">
                <img src="assets/images/IES-foto-3.jpg" alt="Patio del instituto">
                <img src="assets/images/IES-foto-4.jpg" alt="Aula equipada del centro">
            </div>
        </div>
    </section>

    <!-- Sección de contacto con dirección, teléfono y correo del centro -->
    <section class="seccion" id="contacto">
        <div class="seccion-contenido">
            <h2 class="seccion-titulo">Contacto</h2>
            <p><strong>Dirección:</strong> Paseo Ruiseñores, 49-51, 50006 Zaragoza</p>
            <p><strong>Teléfono:</strong> 976 25 93 83</p>
            <p><strong>Email:</strong> iesmsezaragoza@educa.aragon.es</p>
        </div>
    </section>

</main>
