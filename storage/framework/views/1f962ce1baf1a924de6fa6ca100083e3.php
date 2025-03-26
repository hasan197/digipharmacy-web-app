<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title>DigiPharmacy</title>        
        <?php if(app()->environment('production')): ?>
            <?php
                $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            ?>
            <link rel="stylesheet" href="/build/assets/app.css">
            <script type="module" src="/build/<?php echo e($manifest['resources/js/app.tsx']['file']); ?>" defer></script>
        <?php else: ?>
            <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.tsx']); ?>
        <?php endif; ?>
    </head>
    <body class="bg-background text-foreground antialiased <?php echo e(Auth::check() ? 'user-logged-in' : ''); ?>">
        <div id="root"></div>
    </body>
</html>
<?php /**PATH /var/www/resources/views/app.blade.php ENDPATH**/ ?>