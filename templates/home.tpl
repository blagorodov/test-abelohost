{extends file="layout.tpl"}
{block name="content"}
{foreach $categories as $category}
<section class="category">
    <h2 class="category__title">{$category.name|escape}</h2>
    <ul class="category__posts">
        {foreach $category.posts as $post}
        <li class="category__post">
            <a href="/post/{$post.id}"><img src="{$post.image|escape}" alt="{$post.title|escape}" width="400" height="300"></a>
            <h3>{$post.title|escape}</h3>
            <time datetime="{$post.published_at|date_format:'Y-m-d'}">{$post.published_at|date_format:'d.m.Y'}</time>
            <p>{$post.description|escape}</p>
        </li>
        {/foreach}
    </ul>
    <a class="category__all" href="/category/{$category.id}">Все статьи</a>
</section>
{foreachelse}
<p>Статей пока нет</p>
{/foreach}
{/block}
