<!DOCTYPE html>
<html lang="$ContentLocale">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

		<% base_tag %>
		<title><% if Title != Home %>$Title | <% end_if %>$SiteConfig.Title<% if SiteConfig.Tagline %> - $SiteConfig.Tagline<% end_if %></title>

		$MetaTags(false)

		<% if SiteConfig.IconID %>
		<link rel="shortcut icon" href="$SiteConfig.FaviconPath" />
		<% end_if %>
		<% if RssLink %>
		<link href="$RssLink" rel="alternate" type="application/rss+xml" title="$RssTitle" />
		<% end_if %>

		<!--[if lt IE 9]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body id="$ClassName" class="typography" style="$SiteConfig.BackgroundImageStyles">
		<% include Header %>
		<div id="main">
			$Layout
		</div>
		<% include Footer %>
		$BetterNavigator
	</body>
</html>