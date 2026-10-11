{extends file="layout.tpl"}
{block name="content"}
{if $categories|@count == 0}
<p>Статей пока нет</p>
{else}
{foreach $categories as $category}
<section class="category">
    <h2>{$category.name|escape}</h2>
    <ul class="posts">
        {foreach $category.posts as $post}
        <li class="post">
            <img src="{$post.image|escape}" alt="{$post.title|escape}" width="400" height="300">
            <h3>{$post.title|escape}</h3>
            <time datetime="{$post.published_at_iso|escape}">{$post.published_at|escape}</time>
            <p>{$post.description|escape}</p>
        </li>
        {/foreach}
    </ul>
    <a class="all-posts" href="/category/{$category.id}">Все статьи</a>
</section>
{/foreach}
{/if}
{/block}
