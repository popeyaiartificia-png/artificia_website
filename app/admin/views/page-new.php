<?php /** @var string $kind; @var array $form, $errors, $templates */
$noun = $kind === 'post' ? 'post' : 'page';
$base = $kind === 'post' ? '/admin/posts' : '/admin/pages';
$prefix = $kind === 'post' ? '/blog/' : '/';
$fe = $errors['fields'];
$other = array_values(array_diff($errors['all'], $fe));
?>
<a class="back" href="<?= $base ?>">&larr; All <?= $noun ?>s</a>
<?= admin_page_head($kind === 'post' ? 'Posts' : 'Pages', "New $noun", null, "Give it a title and a web address. You add the content and publish it in the editor; until then it is a draft.") ?>

<form class="card card--narrow" method="post" action="<?= $base ?>/new">
  <?= csrf_field() ?>
<?php if ($other): ?><p class="notice notice--error" role="alert"><?= e($other[0]) ?></p><?php endif ?>
  <?= admin_field('title', 'Title', $form['title'], $fe['title'] ?? null, 'text', '', 'required maxlength="255" autocomplete="off" data-slug-to="#f-slug"') ?>
  <?= admin_field('slug', 'Web address', $form['slug'], $fe['slug'] ?? null, 'text', "Lowercase letters, numbers and hyphens, like ai-for-dentists. It lives at $prefix" . ($kind === 'post' ? 'your-address.' : 'your-address (up to two slashes, like services/seo).'), 'required maxlength="190" autocomplete="off" spellcheck="false"') ?>
<?php if ($templates): ?>
  <fieldset class="starter">
    <legend>Start from</legend>
    <div class="check"><label><input type="radio" name="template_id" value=""<?= positive_int($form['template_id']) ? '' : ' checked' ?>> A blank page</label></div>
<?php foreach ($templates as $t): $n = count(json_decode($t['sections'], true) ?: []); ?>
    <div class="check"><label><input type="radio" name="template_id" value="<?= (int) $t['id'] ?>"<?= positive_int($form['template_id']) === (int) $t['id'] ? ' checked' : '' ?>> The &ldquo;<?= e($t['name']) ?>&rdquo; template <span class="muted">(<?= $n ?> <?= $n === 1 ? 'section' : 'sections' ?>)</span></label></div>
<?php endforeach ?>
  </fieldset>
<?php endif ?>
  <button class="btn btn--primary" type="submit" data-busy="Creating&hellip;">Create draft</button>
</form>
