<?php /** @var string $title, $description, $canonical, $robots, $ogType, $body, $ga4, $pixel, $gsc; @var ?string $ogImage; @var array $jsonld */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if ($description !== ''): ?>
<meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if ($robots !== ''): ?>
<meta name="robots" content="<?= e($robots) ?>">
<?php endif; ?>
<?php if ($canonical !== ''): ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php endif; ?>
<meta property="og:site_name" content="Artificia AI">
<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta name="twitter:card" content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<?php if ($ogImage): ?>
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php endif; ?>
<?php if ($gsc !== ''): ?>
<meta name="google-site-verification" content="<?= e($gsc) ?>">
<?php endif; ?>
<?php if ($jsonld): ?>
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $jsonld], JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>
<script>document.documentElement.classList.add('js')</script>
<link rel="stylesheet" href="/assets/css/site.css">
<?php if ($ga4 !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $ga4 ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= $ga4 ?>');</script>
<?php endif; ?>
<?php if ($pixel !== ''): ?>
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '<?= $pixel ?>');fbq('track', 'PageView');</script>
<noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id=<?= $pixel ?>&amp;ev=PageView&amp;noscript=1"></noscript>
<?php endif; ?>
<script src="/assets/js/site.js" defer></script>
</head>
<body>
<main>
<?= $body ?>
</main>
</body>
</html>
