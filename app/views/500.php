<?php /** @var string $wa. Shown by error_page() when the site itself is failing: plain HTML and static files only, no DB, no helpers that need one. */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Something went wrong | Artificia AI</title>
<link rel="stylesheet" href="/assets/css/site.css">
<link rel="icon" href="/assets/img/favicon.png" type="image/png">
<meta name="theme-color" content="#15160F">
</head>
<body>
<header class="bind top">
  <div class="wrap top__in">
    <a class="brand" href="/"><img src="/assets/img/artificia-logo.png" width="492" height="138" alt="Artificia AI home"></a>
  </div>
</header>
<main id="main" class="sheet">
<section class="sec pagehead">
  <div class="wrap">
    <div class="sec__head"><h1 class="h1 h1--page">Something went <em>wrong</em></h1></div>
    <p class="lede">This page could not load just now. Try again in a few minutes, or message us on WhatsApp and we&rsquo;ll help you straight away.</p>
    <p><a class="btn btn--lime" href="<?= e($wa) ?>">Message us on WhatsApp</a></p>
  </div>
</section>
</main>
</body>
</html>
