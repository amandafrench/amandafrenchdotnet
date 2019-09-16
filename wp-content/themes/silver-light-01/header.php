<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="description" content="Personal site of Amanda L. French. Ph.D., who specializes in digital humanities research, teaching, grant writing, consulting, and project management; scholarly communication; poetic form; and 19th- and 20th-century British and Irish literature." />
	<meta name="keywords" content="Amanda French, Amanda L. French, villanelle, rondeau, rondel, triolet, French forms, poetic form, form, Dylan Thomas, Elizabeth Bishop, Edmund Gosse, digital humanities, digital libraries, libraries, technology, consulting" />
	<title>
<?php bloginfo('name'); ?>
<?php if ( is_single() ) { ?>
		&raquo; Blog Archive
<?php } ?>
<?php wp_title(); ?>
	</title>
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
<!-- leave this for stats -->
	<meta name="google-site-verification" content="yTVRxYJPc-0927OHYIeaBmzH7XwYfaimO5ZQQ4XQIZY" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/favicon.ico" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>
<body>
<div id="header">
	<h1 class="blogtitle"><a href="<?php echo get_option('home'); ?>/" class="blogtitle">
<?php bloginfo('name'); ?>
		</a></h1>
	<p class="desc">
<?php bloginfo('description'); ?>
	</p>

</div>
<div id="ddnav" class="nowrap">
	<div id="nav" class="nowrap">
		<ul class="nav">
			<li class="nowrap">
				<a href="<?php bloginfo('url'); ?>">Home</a>
			</li>
<?php wp_list_pages('title_li='); ?>
			<li class="nowrap">
				<a href="http://amandafrench.net/villanelle">Villanelle</a>
			</li>
			<li class="nowrap">
			<a href="http://steepletoplibrary.org">Millay's Library</a>
			</li>
		</ul>
	</div>
</div>
<div id="top">
</div>
<div id="main">
</div>
<div id="top">
</div>
<div id="main">
