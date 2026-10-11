{extends file="layout.tpl"}
{block name="content"}
<section class="category-page">
    <p class="category-page__home"><a href="/">На главную</a></p>
    <h1>{$category.name|escape}</h1>
    <p class="category-page__description">{$category.description|escape}</p>

    <p class="category-page__sort">
        <a href="/category/{$category.id}">По дате</a>
        <a href="/category/{$category.id}?sort=views">По просмотрам</a>
    </p>

    <ul class="category-page__list">
    {foreach $posts as $post}
        <li class="category-page__item">
            <img src="{$post.image|escape}" alt="{$post.title|escape}" width="120" height="90">
            <div>
                <h2>{$post.title|escape}</h2>
                <time datetime="{$post.published_at_iso|escape}">{$post.published_at|escape}</time>
                <p class="category-page__views">{$post.views} просмотров</p>
                <p>{$post.description|escape}</p>
                <a href="/post/{$post.id}">Полный текст</a>
            </div>
        </li>
    {/foreach}
    </ul>

    {if $page_count > 1}
    <nav class="category-page__pages" aria-label="Страницы">
        {if $page > $page_count}
            {if $sort == 'views'}
            <a class="category-page__page-link" href="/category/{$category.id}?sort=views&amp;page={$page_count}">Предыдущая</a>
            {else}
            <a class="category-page__page-link" href="/category/{$category.id}?page={$page_count}">Предыдущая</a>
            {/if}
        {elseif $page > 1}
            {if $sort == 'views'}
            <a class="category-page__page-link" href="/category/{$category.id}?sort=views&amp;page={$page-1}">Предыдущая</a>
            {else}
            <a class="category-page__page-link" href="/category/{$category.id}?page={$page-1}">Предыдущая</a>
            {/if}
        {else}
        <span class="category-page__page-inactive">Предыдущая</span>
        {/if}

        {for $n=1 to $page_count}
            {if $n == $page}
            <span class="category-page__page-inactive">{$n}</span>
            {elseif $sort == 'views'}
            <a class="category-page__page-link" href="/category/{$category.id}?sort=views&amp;page={$n}">{$n}</a>
            {else}
            <a class="category-page__page-link" href="/category/{$category.id}?page={$n}">{$n}</a>
            {/if}
        {/for}

        {if $page < $page_count}
            {if $sort == 'views'}
            <a class="category-page__page-link" href="/category/{$category.id}?sort=views&amp;page={$page+1}">Следующая</a>
            {else}
            <a class="category-page__page-link" href="/category/{$category.id}?page={$page+1}">Следующая</a>
            {/if}
        {else}
        <span class="category-page__page-inactive">Следующая</span>
        {/if}
    </nav>
    {/if}
</section>
{/block}
