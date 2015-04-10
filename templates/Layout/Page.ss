<div class="container main-container">
	<div class="grid">
		<% if Menu(2) %>
		<div class="column-4 sidebar">
			<ul>
				<% loop Menu(2) %>
				<li class="$LinkingMode"><a href="$Link">$MenuTitle</a></li>
				<% end_loop %>
			</ul>
		</div>
		<div class="column-8">
			<h1>$Title</h1>
			<div class="content">$Content</div>
			$Form
			$PageComments
		</div>
		<% else %>
		<div class="column-12">
			<h1>$Title</h1>
			<div class="content">$Content</div>
			$Form
			$PageComments
		</div>
		<% end_if %>
	</div>
</div>