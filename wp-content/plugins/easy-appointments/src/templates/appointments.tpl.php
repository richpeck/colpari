<script type="text/template" id="ea-appointments-main">
<?php 
	get_current_screen()->render_screen_meta();
?>
	<div class="wrap">
		<h2><?php _e('Appointments', 'easy-appointments');?></h2>
		<br>
		<table id="ea-appointments-table-filter" class="filter-part wp-filter ea-responsive-table">
			<tbody>
				<tr>
					<td class="filter-label"><label for="ea-filter-locations"><strong><?php _e('Location', 'easy-appointments');?> :</strong></label></td>
					<td class="filter-select">
						<select name="ea-filter-locations" id="ea-filter-locations" data-c="location">
							<option value="">-</option>
							<% _.each(cache.Locations,function(item,key,list){ %>
								<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
							<% });%>
						</select>
					</td>
					<td class="filter-label"><label for="ea-filter-services"><strong><?php _e('Service', 'easy-appointments');?> :</strong></label></td>
					<td class="filter-select">
						<select name="ea-filter-services" id="ea-filter-services" data-c="service">
							<option value="">-</option>
							<% _.each(cache.Services,function(item,key,list){ %>
								<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
							<% });%>
						</select>
					</td>
					<td class="filter-label"><label for="ea-filter-workers"><strong><?php _e('Worker', 'easy-appointments');?> :</strong></label></td>
					<td class="filter-select">
						<select name="ea-filter-workers" id="ea-filter-workers" data-c="worker">
							<option value="">-</option>
							<% _.each(cache.Workers,function(item,key,list){ %>
							<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
							<% });%>
						</select>
					</td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td class="filter-label"><label for="ea-filter-status"><strong><?php _e('Status', 'easy-appointments');?> :</strong></label></td>
					<td class="filter-select">
						<select name="ea-filter-status" id="ea-filter-status" data-c="status">
							<option value="">-</option>
							<% _.each(cache.Status,function(item,key,list){ %>
								<option value="<%= key %>"><%= item %></option>
							<% });%>
						</select>
					</td>
					<td class="filter-label"><label for="ea-filter-from"><strong><?php _e('From', 'easy-appointments');?> :</strong></label></td>
					<td><input class="date-input" type="text" name="ea-filter-from" id="ea-filter-from" data-c="from"></td>
					<td class="filter-label"><label for="ea-filter-to"><strong><?php _e('To', 'easy-appointments');?> :</strong></label></td>
					<td><input class="date-input" type="text" name="ea-filter-to" id="ea-filter-to" data-c="to"></td>
					<td class="filter-label"><strong><?php _e('Quick time filter', 'easy-appointments');?>:</strong>
						<select id="ea-period">
							<option value=""><?php _e('Select period', 'easy-appointments');?></option>
							<option value="week"><?php _e('This week', 'easy-appointments');?></option>
							<option value="month"><?php _e('This month', 'easy-appointments');?></option>
							<option value="today"><?php _e('Today', 'easy-appointments');?></option>
						</select>
					</td>
					<td></td>
				</tr>
			</tbody>
		</table>
		<h2>
			<a href="#" class="add-new-h2 add-new">
				<i class="fa fa-plus"></i>
				<?php _e('Add New Appointment', 'easy-appointments');?>
			</a>
			<a href="#" class="add-new-h2 refresh-list">
				<i class="fa fa-refresh"></i>
				<?php _e('Refresh', 'easy-appointments');?>
			</a>
			<span id="status-msg" class="status"></span>
		</h2>

		<table class="ea-responsive-table widefat fixed">
			<thead>
				<tr>
					<th colspan="2" class="manage-column column-title">Id / <?php _e('Location', 'easy-appointments');?> / <?php _e('Service', 'easy-appointments');?> / <?php _e('Worker', 'easy-appointments');?></th>
					<th colspan="2" class="manage-column column-title"><?php _e('Customer', 'easy-appointments');?></th>
					<th class="manage-column column-title"><?php _e('Descrtiption', 'easy-appointments');?></th>
					<th class="manage-column column-title"><?php _e('Date & time', 'easy-appointments');?></th>
					<th class="manage-column column-title"><?php _e('Status', 'easy-appointments');?> / <?php _e('Price', 'easy-appointments');?> / <?php _e('Created', 'easy-appointments');?></th>
					<th class="manage-column column-title"><?php _e('Action', 'easy-appointments');?></th>
				</tr>
			</thead>
			<tbody id="ea-appointments">
			</tbody>
		</table>
	</div>
</script>

<script type="text/template" id="ea-tpl-appointment-row">
	<td colspan="2" class="post-title page-title column-title">
		<strong>#<%= row.id %></strong>
		<strong><%= _.escape( _.findWhere(cache.Locations, {id:row.location}).name ) %></strong>
		<strong><%= _.escape( _.findWhere(cache.Services, {id:row.service}).name ) %></strong>
		<strong><%= _.escape( _.findWhere(cache.Workers, {id:row.worker}).name ) %></strong>
	</td>
	<td colspan="2">
		<% _.each(cache.MetaFields,function(item,key,list) { %>
			<% if (row[item.slug] !== "undefined" && item.type !== 'TEXTAREA') { %>
			<strong><%= _.escape(row[item.slug]) %></strong><br>
			<% } %>
		<% });%>
	</td>
	<td>
		<% _.each(cache.MetaFields,function(item,key,list) { %>
			<% if (row[item.slug] !== "undefined" && item.type === 'TEXTAREA') { %>
			<strong><%= _.escape(row[item.slug]) %></strong><br>
			<% } %>
		<% });%>
	</td>
	<td>
		<strong><%= _.formatDate(row.date) %> - <%= _.formatTime(row.start) %></strong><br>
		<strong><%= _.formatDate(row.end_date) %> - <%= _.formatTime(row.end) %></strong>
	</td>
	<td>
		<strong><%= eaData.Status[row.status] %></strong><br>
		<!-- <strong><%= row.user %></strong><br> -->
		<strong><%= row.price %></strong><br>
		<strong><%= _.formatDateTime(row.created) %></strong>
	</td>
	<td class="action-center">
		<button class="button btn-edit"><?php _e('Edit', 'easy-appointments');?></button>
		<button class="button btn-del"><?php _e('Delete', 'easy-appointments');?></button>
		<button class="button btn-clone"><?php _e('Clone', 'easy-appointments');?></button>
	</td>
</script>

<script type="text/template" id="ea-tpl-appointment-row-edit">
<td colspan="8">
	<table class="inner-edit-table ea-responsive-table">
		<tbody>
			<tr>
				<td colspan="2">
					<select class="app-fields" name="ea-input-locations" id="ea-input-locations" data-prop="location">
						<option value=""> -- <?php _e('Location', 'easy-appointments');?> -- </option>
						<% _.each(cache.Locations,function(item,key,list){
						if (item.id == row.location) { %>
							<option value="<%= item.id %>" selected="selected"><%= _.escape(item.name) %></option>
						<% } else { %>
							<option value="<%= item.id %>"><%= _.escape(item.name) %></option>
						<% }
						});%>
					</select><br>
					<select class="app-fields ea-service" name="ea-input-services" id="ea-input-services" data-prop="service">
						<option value=""> -- <?php _e('Service', 'easy-appointments');?> -- </option>
						<% _.each(cache.Services,function(item,key,list){
							if (item.id == row.service) { %>
								<option value="<%= item.id %>" data-duration="<%= item.duration %>" data-price="<%= item.price %>" selected="selected"><%= _.escape( item.name ) %></option>
						<% } else { %>
								<option value="<%= item.id %>" data-duration="<%= item.duration %>"  data-price="<%= item.price %>"><%= _.escape( item.name ) %></option>
						<% }
						});%>
					</select><br>
					<select class="app-fields" name="ea-input-workers" id="ea-input-workers" data-prop="worker">
						<option value=""> -- <?php _e('Worker', 'easy-appointments');?> -- </option>
						<% _.each(cache.Workers,function(item,key,list){
							if(item.id == row.worker) { %>
								<option value="<%= item.id %>" selected="selected"><%= _.escape( item.name ) %></option>
						<% } else { %>
								<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
						<% }
						});%>
					</select>
				</td>
				<td colspan="2">
					<% _.each(cache.MetaFields,function(item,key,list) { %>
						<% if(item.type === 'INPUT') { %>
						<input type="text" data-prop="<%= item.slug %>" placeholder="<%= _.escape( item.label ) %>" value="<% if (typeof row[item.slug] !== "undefined") { %><%= _.escape( row[item.slug] ) %><% } %>"><br>
                        <% } else if(item.type === 'PHONE') { %>
                        <input type="text" data-prop="<%= item.slug %>" placeholder="<%= _.escape( item.label ) %>" value="<% if (typeof row[item.slug] !== "undefined") { %><%= _.escape( row[item.slug] ) %><% } %>"><br>
                        <% } else if(item.type === 'SELECT') { %>
							<select data-prop="<%= item.slug %>">
								<% _.each(item.mixed.split(','),function(i,k,l) {
									if(typeof row[item.slug] !== 'undefined' && i === row[item.slug]) { %>
								%>
								<option value="<%= i %>" selected><%= _.escape( i ) %></option>
								<% } else { %>
								<option value="<%= i %>" ><%= _.escape( i ) %></option>
								<% }});%>
							</select>
						<% } %>
					<% });%>
				</td>
				<td colspan="2">
					<% _.each(cache.MetaFields,function(item,key,list) { %>
						<% if(item.type === 'TEXTAREA') { %>
						<textarea rows="3" data-prop="<%= item.slug %>" placeholder="<%= item.label %>"><% if (typeof row[item.slug] !== "undefined") { %><%= _.escape( row[item.slug] ) %><% } %></textarea><br>
						<% } %>
					<% });%>
				</td>
				<td>
					<p><?php _e('Date', 'easy-appointments');?> :</p>
					<input id="date-start" class="app-fields date-start" type="text" data-prop="date" value="<%= row.date %>"><br>
					<p><?php _e('Time', 'easy-appointments');?> :</p>
					<select data-prop="start" disabled="disabled" class="time-start">
					</select>
				</td>
				<td>
					<select name="ea-select-status" data-prop="status">
						<% _.each(cache.Status,function(item,key,list){
							if(key == row.status) { %>
								<option value="<%= key %>" selected="selected"><%= item %></option>
						<% } else { %>
								<option value="<%= key %>"><%= item %></option>
						<% }
						});%>
					</select>
					<span><?php _e('Price', 'easy-appointments');?> : </span><input class="ea-price" style="width: 50px" type="text" data-prop="price" value="<%= row.price %>">
					<!-- <strong><%= row.user %></strong><br>
					<strong><%= row.created %></strong>-->
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<label for="send-mail"> <?php _e('Send email notification :', 'easy-appointments');?> </label>
					<input name="send-mail" type="checkbox" checked="checked">
				</td>
				<td colspan="2" style="text-align: right;">
					<button class="button button-primary btn-save"><?php _e('Save', 'easy-appointments');?></button>
					<button class="button btn-cancel"><?php _e('Cancel', 'easy-appointments');?></button>
				</td>
			</tr>
		</tbody>
	</table>
</td>
</script>

<script type="text/template" id="ea-tpl-appointment-times">
<% _.each(times,function(item,key,list){ 
	if(app.start === item.value) { %>
	<option value="<%= item.value %>" selected="selected"><%= item.show %></option>
	<% } else { %>
		<option value="<%= item.value %>" <% if(item.count < 1) {%>disabled<% } %>><%= item.show %></option>
	<% } %>
<% });%>
</script>