{extends file="layout.tpl"}
{block name="content"}
<section class="post-page">
    <img class="post-page__image" src="{$post.image|escape}" alt="{$post.title|escape}" width="800" height="600">
    <h1>{$post.title|escape}</h1>
    <time datetime="{$post.published_at|date_format:'Y-m-d'}">{$post.published_at|date_format:'d.m.Y'}</time>
    <p class="post-page__views">{$post.views} просмотров</p>
    <p class="post-page__description">{$post.description|escape}</p>
    <p class="post-page__categories">
    {foreach $post.categories as $category}
        <a href="/category/{$category.id}">{$category.name|escape}</a>{if !$category@last}, {/if}
    {/foreach}
    </p>
    <div class="post-page__body">
    {foreach $paragraphs as $paragraph}
        <p>{$paragraph|escape}</p>
    {/foreach}
    </div>

    {if $related}
    <section class="post-page__related">
        <h2>Похожие статьи</h2>
        <ul>
        {foreach $related as $item}
            <li>
                <img src="{$item.image|escape}" alt="{$item.title|escape}" width="120" height="90">
                <div>
                    <a href="/post/{$item.id}">{$item.title|escape}</a>
                    <time datetime="{$item.published_at|date_format:'Y-m-d'}">{$item.published_at|date_format:'d.m.Y'}</time>
                </div>
            </li>
        {/foreach}
        </ul>
    </section>
    {/if}
</section>
{/block}
