<div class="container">
    <!-- Main hero unit for a primary marketing message or call to action -->
    <div class="hero-unit">
        <h1>Admin Module</h1>
        <p>This is a template for a simple marketing or informational website. It includes a large callout called the hero unit and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
        <p>
            <a class="btn btn-primary btn-large" href="<?php echo App::admin()->url(); ?>documents/"><i class="icon-white icon-file"></i> Documents (DB test)</a>
        </p>

    </div>

    <?php include App::main()->templatesPath()."debug.php" ?>
</div>