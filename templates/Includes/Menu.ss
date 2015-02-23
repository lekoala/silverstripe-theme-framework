<div id="main-menu-container">
	<div class="container">
		<div id="main-menu">
			<nav id="main-nav" data-toggle-label="Menu">
				<ul class="menu">
					<% loop Menu(1) %>
					<li class="$LinkingMode $FirstLast">
						<a href="$Link">$MenuTitle</a>
						<% if Children %>
						<ul class="sub-menu">
							<% loop Children %>
							<% if ShowInMenus %>
							<li class="$LinkingMode $FirstLast"><a href="$Link">$MenuTitle</a></li>
							<% end_if %>
							<% end_loop %>
						</ul>
						<% end_if %>
					</li>
					<% end_loop %>
				</ul>
			</nav>	
		</div>
	</div>
</div>