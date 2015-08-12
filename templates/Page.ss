<!DOCTYPE html>
<html lang="$ContentLocale">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

		<% base_tag %>
		<title><% if Title != Home %>$Title | <% end_if %>$SiteConfig.Title<% if SiteConfig.Tagline %> - $SiteConfig.Tagline<% end_if %></title>

		<meta property="og:image" content="$OpenGraphImage"/>
		<meta property="og:title" content="$OpenGraphTitle"/>
		<meta property="og:site_name" content="$SiteConfig.Title"/>

		$SiteConfig.HeadScripts
		$MetaTags(false)

		<% if SiteConfig.IconID %>
		<link rel="shortcut icon" href="$SiteConfig.FaviconPath" />
		<% else %>
		<link rel="shortcut icon" href="$ThemeDir/images/favicon.ico" />
		<% end_if %>
		<% if RssLink %>
		<link href="$RssLink" rel="alternate" type="application/rss+xml" title="$RssTitle" />
		<% end_if %>

		<!--[if lt IE 9]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<% if SiteConfig.GoogleFonts %>
		<link href='//fonts.googleapis.com/css?{$SiteConfig.GoogleFonts}' rel='stylesheet' type='text/css'>
		<% end_if %>
	</head>
	<body id="$ClassName" class="typography <% if IframeLayout %>iframe<% end_if %>" style="$SiteConfig.BackgroundImageStyles">
		<% if not IframeLayout %>
		<% include Header %>
		<% end_if %>
		<div id="main">
			$Layout
		</div>
		<% if not IframeLayout %>
		<% include Footer %>
		$BetterNavigator
		<% end_if %>
		<% if SiteConfig.GoogleAnalyticsEnabled %>
		<% include GoogleAnalyticsSnippet %>
		<% end_if %>
		<% if OutdatedBrowserEnabled %>
		<% include OutdatedBrowserSnippet %>
		<% end_if %>
	</body>
</html>