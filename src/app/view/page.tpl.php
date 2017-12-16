<!DOCTYPE html>
<html>
  <head>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="<?= SITE_ROOT ?>css/base.css" />
    <link rel="stylesheet" href="<?= SITE_ROOT ?>css/site.css" />
    <link rel="stylesheet" href="<?= SITE_ROOT ?>css/font-awesome.min.css" />

    <script type="text/javascript" src="<?= SITE_ROOT ?>js/underscore-min.js"></script>
    <script type="text/javascript" src="<?= SITE_ROOT ?>js/base.js"></script>
    <script type="text/javascript" src="<?= SITE_ROOT ?>js/site.js"></script>
  </head>

  <body>
    <div id="page">
      <div id="header">
      </div>
      <hr class="hidden">

      <div id="body">
        <?= $data->body ?>
      </div>
    </div>

    <script type="text/javascript">
    </script>
  </body>
</html>