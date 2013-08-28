<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/09/sitemap.xsd"	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> 
{foreach $_urls as $url_dat}
<url>
	<loc>{$_home}/{$url_dat.path}</loc> 
	<lastmod>{$url_dat.lastmod}</lastmod> 
	<changefreq>{$url_dat.changefreq}</changefreq> 
	<priority>{$url_dat.priority}</priority> 
</url>
{/foreach}
</urlset>