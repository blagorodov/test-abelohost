<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$page_title|default:'Блог'|escape}</title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <header>{if $is_home|default:false}Блог{else}<a href="/">Блог</a>{/if}</header>
    <main>
    {block name="content"}{/block}
    </main>
</body>
</html>
