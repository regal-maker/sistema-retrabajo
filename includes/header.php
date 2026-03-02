<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<link rel="stylesheet" href="assets/css/style.css">

<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#004a99">

<script>
  // Registrar el Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('sw.js').then(function(registration) {
        console.log('ServiceWorker registrado con éxito con alcance: ', registration.scope);
      }, function(err) {
        console.log('Falló el registro del ServiceWorker: ', err);
      });
    });
  }
</script>