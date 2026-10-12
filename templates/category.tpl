{extends file="layout.tpl"}
{block name="content"}
<section class="category-page">
    <h1>{$category.name|escape}</h1>
    <p class="category-page__description">{$category.description|escape}</p>

    <p class="category-page__sort">
        <a href="/category/{$category.id}?sort=date"{if $sort == 'date'} aria-current="true"{/if}>По дате</a>
        <a href="/category/{$category.id}?sort=views"{if $sort == 'views'} aria-current="true"{/if}>По просмотрам</a>
    </p>

    {if $posts}
    <ul class="category-page__list">
    {foreach $posts as $post}
        <li class="category-page__item">
            <a href="/post/{$post.id}"><img src="{$post.image|escape}" alt="{$post.title|escape}" width="120" height="90"></a>
            <div>
                <h2>{$post.title|escape}</h2>
                <time datetime="{$post.published_at|date_format:'Y-m-d'}">{$post.published_at|date_format:'d.m.Y'}</time>
                <p class="category-page__views">{$post.views} просмотров</p>
                <p>{$post.description|escape}</p>
                <a href="/post/{$post.id}">Полный текст</a>
            </div>
        </li>
    {/foreach}
    </ul>
    {else}
    <p>В этой категории пока нет статей</p>
    {/if}

    {if $page_count > 1}
    <nav class="category-page__pages" aria-label="Страницы">
        {if $page > 1}
        <a class="category-page__page-link" href="/category/{$category.id}?sort={$sort}&amp;page={$page-1}">Предыдущая</a>
        {else}
        <span class="category-page__page-inactive">Предыдущая</span>
        {/if}

        {for $n=1 to $page_count}
            {if $n == $page}
            <span class="category-page__page-inactive" aria-current="page">{$n}</span>
            {else}
            <a class="category-page__page-link" href="/category/{$category.id}?sort={$sort}&amp;page={$n}">{$n}</a>
            {/if}
        {/for}

        {if $page < $page_count}
        <a class="category-page__page-link" href="/category/{$category.id}?sort={$sort}&amp;page={$page+1}">Следующая</a>
        {else}
        <span class="category-page__page-inactive">Следующая</span>
        {/if}
    </nav>
    {/if}
</section>
{/block}
