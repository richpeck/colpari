<script type="text/template" id="ea-settings-main">
<?php 
	get_current_screen()->render_screen_meta();
?>
	<div class="wrap">
		<ul id="tab-header">
			<li>
				<a href="#locations/">
					<i class="fa fa-map-marker"></i>
					<?php _e('Locations', 'easy-appointments');?>
				</a>
			</li>
			<li>
				<a href="#services/">
					<i class="fa fa-cube"></i>
					<?php _e('Services', 'easy-appointments');?>
				</a>
			</li>
			<li>
				<a href="#staff/">
					<i class="fa fa-user"></i>
					<?php _e('Workers', 'easy-appointments');?>
				</a>
			</li>
			<li>
				<a href="#connection/">
					<i class="fa fa-sitemap"></i>
					<?php _e('Connections', 'easy-appointments');?>
				</a>
			</li>
			<li>
				<a href="#custumize/">
					<i class="fa fa-paint-brush"></i>
					<?php _e('Customize', 'easy-appointments');?>
				</a>
			</li>
			<li>
				<a href="#tools/">
					<i class="fa fa-bug" aria-hidden="true"></i>
					<?php _e('Tools', 'easy-appointments');?>
				</a>
			</li>
		</ul>
		<div id="tab-content">
		</div>
	</div>
</script>

<script type="text/template" id="ea-tpl-locations-table">
<div>
    <div>
        <a href="#" class="add-new-h2 add-new">
            <i class="fa fa-plus"></i>
            <?php _e('Add New Location', 'easy-appointments'); ?>
        </a>
        <a href="#" class="add-new-h2 refresh-list">
            <i class="fa fa-refresh"></i>
            <?php _e('Refresh', 'easy-appointments'); ?>
        </a>
        <div class="ea-sort-fields">
            <label><?php _e('Sort Locations By');?>:</label>
            <select id="sort-locations-by" name="sort-locations-by">
                <option value="id">Id</option>
                <option value="name">Name</option>
                <option value="address">Address</option>
                <option value="location">Location</option>
            </select>
            <label><?php _e('Order by');?>:</label>
            <select id="order-locations-by" name="order-locations-by">
                <option value="ASC">asc</option>
                <option value="DESC">desc</option>
            </select>
        </div>
        <span id="status-msg" class="status"></span>
    </div>
    <table class="wp-list-table widefat fixed">
        <thead>
        <tr>
            <th class="manage-column column-title column-5">Id</th>
            <th class="manage-column column-title"><?php _e('Name', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Address', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Location', 'easy-appointments'); ?></th>
            <th class="manage-column column-title column-15"><?php _e('Actions', 'easy-appointments'); ?></th>
        </tr>
        </thead>
        <tbody id="ea-locations"></tbody>
    </table>
</div>
</script>

<script type="text/template" id="ea-tpl-locations-row">
	<td><%= row.id %></td>
	<td class="post-title page-title column-title">
		<strong><%= _.escape( row.name ) %></strong>
	</td>
	<td>
		<strong><%= _.escape( row.address ) %></strong>
	</td>
	<td>
		<strong><%= _.escape( row.location ) %></strong>
	</td>
	<td>
		<button class="button btn-edit"><?php _e('Edit','easy-appointments');?></button>
		<button class="button btn-del"><?php _e('Delete','easy-appointments');?></button>
	</td>
</script>

<script type="text/template" id="ea-tpl-locations-row-edit">
    <td><%= row.id %></td>
    <td><input type="text" data-prop="name" value="<%= _.escape( row.name ) %>"></td>
    <td><input type="text" data-prop="address" value="<%= _.escape( row.address ) %>"></td>
    <td><input type="text" data-prop="location" value="<%= _.escape( row.location ) %>"></td>
    <td>
        <button class="button button-primary btn-save"><?php _e('Save', 'easy-appointments'); ?></button>
        <button class="button btn-cancel"><?php _e('Cancel', 'easy-appointments'); ?></button>
    </td>
</script>

<script type="text/template" id="ea-tpl-services-table">
<div>
    <div>
        <a href="#" class="add-new-h2 add-new">
            <i class="fa fa-plus"></i>
            <?php _e('Add New Service', 'easy-appointments'); ?>
        </a>
        <a href="#" class="add-new-h2 refresh-list">
            <i class="fa fa-refresh"></i>
            <?php _e('Refresh', 'easy-appointments'); ?>
        </a>
        <div class="ea-sort-fields">
            <label><?php _e('Sort Services By'); ?>:</label>
            <select id="sort-services-by" name="sort-services-by">
                <option value="id">Id</option>
                <option value="name">Name</option>
                <option value="duration">Description</option>
                <option value="price">Price</option>
            </select>
            <label><?php _e('Order by'); ?>:</label>
            <select id="order-services-by" name="order-services-by">
                <option value="ASC">asc</option>
                <option value="DESC">desc</option>
            </select>
        </div>
        <span id="status-msg" class="status"></span>
    </div>
    <table class="wp-list-table widefat fixed">
        <thead>
        <tr>
            <th class="manage-column column-title column-5">Id</th>
            <th class="manage-column column-title"><?php _e('Name', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Duration (in minutes)', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Slot step (in minutes)', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Price', 'easy-appointments'); ?></th>
            <th class="manage-column column-title column-15"><?php _e('Actions', 'easy-appointments'); ?></th>
        </tr>
        </thead>
        <tbody id="ea-services">

        </tbody>
    </table>
</div>
</script>

<script type="text/template" id="ea-tpl-services-row">
    <td><%= row.id %></td>
    <td class="post-title page-title column-title">
        <strong><%= _.escape( row.name ) %></strong>
    </td>
    <td>
        <strong><%= _.escape( row.duration ) %></strong>
    </td>
    <td>
        <strong><%= _.escape( row.slot_step ) %></strong>
    </td>
    <td>
        <strong><%= _.escape( row.price ) %></strong>
    </td>
    <td>
        <button class="button btn-edit"><?php _e('Edit','easy-appointments');?></button>
        <button class="button btn-del"><?php _e('Delete','easy-appointments');?></button>
    </td>
</script>

<script type="text/template" id="ea-tpl-services-row-edit">
	<td><%= row.id %></td>
	<td><input type="text" data-prop="name" value="<%= _.escape( row.name ) %>"></td>
	<td><input type="text" data-prop="duration" value="<%= _.escape( row.duration ) %>"></td>
	<td><input type="text" data-prop="slot_step" value="<%= _.escape( row.slot_step ) %>"></td>
	<td><input type="text" data-prop="price" value="<%= _.escape( row.price ) %>"></td>
	<td>
		<button class="button button-primary btn-save"><?php _e('Save','easy-appointments');?></button>
		<button class="button btn-cancel"><?php _e('Cancel','easy-appointments');?></button>
	</td>
</script>

<!-- Staff -->
<script type="text/template" id="ea-tpl-staff-table">
<div>
    <div>
        <a href="#" class="add-new-h2 add-new">
            <i class="fa fa-plus"></i>
            <?php _e('Add New Worker', 'easy-appointments'); ?>
        </a>
        <a href="#" class="add-new-h2 refresh-list">
            <i class="fa fa-refresh"></i>
            <?php _e('Refresh', 'easy-appointments'); ?>
        </a>
        <div class="ea-sort-fields">
            <label><?php _e('Sort Workers By');?>:</label>
            <select id="sort-workers-by" name="sort-workers-by">
                <option value="id">Id</option>
                <option value="name">Name</option>
                <option value="description">Description</option>
                <option value="email">Email</option>
                <option value="phone">Phone</option>
            </select>
            <label><?php _e('Order by');?>:</label>
            <select id="order-workers-by" name="order-workers-by">
                <option value="ASC">asc</option>
                <option value="DESC">desc</option>
            </select>
        </div>
        <span id="status-msg" class="status"></span>
    </div>
    <table class="wp-list-table widefat fixed">
        <thead>
        <tr>
            <th class="manage-column column-title column-5">Id</th>
            <th class="manage-column column-title"><?php _e('Name', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Description', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Email', 'easy-appointments'); ?></th>
            <th class="manage-column column-title"><?php _e('Phone', 'easy-appointments'); ?></th>
            <th class="manage-column column-title column-15"><?php _e('Actions', 'easy-appointments'); ?></th>
        </tr>
        </thead>
        <tbody id="ea-staff">

        </tbody>
    </table>
</div>
</script>

<script type="text/template" id="ea-tpl-worker-row">
	<td><%= row.id %></td>
	<td class="post-title page-title column-title">
		<strong><%= _.escape( row.name ) %></strong>
	</td>
	<td>
		<strong><%= _.escape( row.description ) %></strong>
	</td>
	<td>
		<strong><%= _.escape( row.email ) %></strong>
	</td>
	<td>
		<strong><%= _.escape( row.phone ) %></strong>
	</td>
	<td>
		<button class="button btn-edit"><?php _e('Edit','easy-appointments');?></button>
		<button class="button btn-del"><?php _e('Delete','easy-appointments');?></button>
	</td>
</script>

<script type="text/template" id="ea-tpl-worker-row-edit">
	<td><%= row.id %></td>
	<td><input type="text" data-prop="name" value="<%= _.escape( row.name ) %>"></td>
	<td><input type="text" data-prop="description" value="<%= _.escape( row.description ) %>"></td>
	<td><input type="text" data-prop="email" value="<%= _.escape( row.email ) %>"></td>
	<td><input type="text" data-prop="phone" value="<%= _.escape( row.phone ) %>"></td>
	<td>
		<button class="button button-primary btn-save"><?php _e('Save','easy-appointments');?></button>
		<button class="button btn-cancel"><?php _e('Cancel','easy-appointments');?></button>
	</td>
</script>

<!-- Connections -->
<script type="text/template" id="ea-tpl-connections-table">
<div>
	<h2>
		<a href="#" class="add-new-h2 add-new">
			<i class="fa fa-plus"></i>
			<?php _e('Add New Connection', 'easy-appointments'); ?>
		</a>
		<a href="#" class="add-new-h2 add-new-bulk">
			<i class="fa fa-plus"></i>
			<?php _e('Bulk Add New Connections', 'easy-appointments'); ?>
		</a>
		<a href="#" class="add-new-h2 refresh-list">
			<i class="fa fa-refresh"></i>
			<?php _e('Refresh', 'easy-appointments'); ?>
		</a>
		<span id="status-msg" class="status"></span>
	</h2>
	<table class="wp-list-table widefat fixed">
		<thead>
		<tr>
			<th colspan="4" class="manage-column column-title">Id / <?php _e('Location', 'easy-appointments'); ?>
				/ <?php _e('Service', 'easy-appointments'); ?> / <?php _e('Worker', 'easy-appointments'); ?></th>
			<th colspan="2" class="manage-column column-title"><?php _e('Days of week', 'easy-appointments'); ?></th>
			<th colspan="2" class="manage-column column-title">
				<?php _e('Time', 'easy-appointments'); ?>
			</th>
			<th colspan="2" class="manage-column column-title">
				<?php _e('Date', 'easy-appointments'); ?>
			</th>
			<th class="manage-column column-title"><?php _e('Is working', 'easy-appointments'); ?></th>
			<th class="manage-column column-title column-15"><?php _e('Actions', 'easy-appointments'); ?></th>
		</tr>
		</thead>
		<tbody id="ea-connections">

		</tbody>
	</table>
	<div id="bulk-connections-builder" style="display: none;">
		<div id="bulk-connections-builder-content" style="width: 100%;"></div>
	</div>
</div>
</script>

<script type="text/template" id="ea-tpl-connection-row">
	<td colspan="4" class="table-row-td">
		#<%= row.id %>
		<br>
		<p> 
			<strong>
				<%= (row.location == 0) ? '-' : _.escape( _.findWhereSafe(locations, row.location, 'name' )) %>
			</strong>
		</p>
		<p>
			<strong>
				<%= (row.service == 0) ? '-' : _.escape( _.findWhereSafe(services, row.service, 'name' )) %>
			</strong>
		</p>
		<p>
			<strong>
				<%= (row.worker == 0) ? '-' : _.escape( _.findWhereSafe(workers, row.worker, 'name' )) %>
			</strong>
		</p>
	</td>
	<% var weekdays = {
			"Monday" : "<?php _e('Monday','easy-appointments');?>",
			"Tuesday": "<?php _e('Tuesday','easy-appointments');?>",
			"Wednesday": "<?php _e('Wednesday','easy-appointments');?>",
			"Thursday": "<?php _e('Thursday','easy-appointments');?>",
			"Friday": "<?php _e('Friday','easy-appointments');?>",
			"Saturday": "<?php _e('Saturday','easy-appointments');?>",
			"Sunday": "<?php _e('Sunday','easy-appointments');?>"
		}; %>
	<td colspan="2">
		<% _.each(row.day_of_week, function(item,key,list) { %>
		<span><%= weekdays[item] %></span><br>
		<% }); %>
	</td>
	<td colspan="2">
		<p class="label-up"><?php _e('Starts at','easy-appointments');?> :</p>
		<strong><%= row.time_from %></strong><br>
		<p class="label-up"><?php _e('ends at','easy-appointments');?> :</p>
		<strong><%= row.time_to %></strong>
	</td>
	<td colspan="2">
		<p class="label-up"><?php _e('Active from','easy-appointments');?> :</p>
		<strong><%= row.day_from %></strong><br>
		<p class="label-up"><?php _e('to','easy-appointments');?> :</p>
		<strong><%= row.day_to %></strong>
	</td>
	<td>
		<strong>
			<% if(row.is_working == 0) { %>
				<?php _e('No','easy-appointments');?>
			<% } else { %>
				<?php _e('Yes','easy-appointments');?>
			<% } %>
		</strong>
	</td>
	<td class="action-center">
		<button class="button btn-edit"><?php _e('Edit','easy-appointments');?></button><br>
		<button class="button btn-del"><?php _e('Delete','easy-appointments');?></button><br>
		<button class="button btn-clone"><?php _e('Clone','easy-appointments');?></button><br>
	</td>
</script>

<script type="text/template" id="ea-tpl-connection-row-edit">
	<td colspan="4">
		#<%= row.id %><br>
		<select data-prop="location">
			<option value=""> -- <?php _e('Location','easy-appointments');?> -- </option>
	<% _.each(locations,function(item,key,list){
		if(item.id == row.location) { %>
			<option value="<%= item.id %>" selected="selected"><%= _.escape( item.name ) %></option>
	<% } else { %>
			<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
	<% }
		});%>
		</select>
		<br>
		<select data-prop="service">
			<option value=""> -- <?php _e('Service','easy-appointments');?> -- </option>
	<% _.each(services,function(item,key,list){
		// create variables
		if(item.id == row.service) { %>
			<option value="<%= item.id %>" selected="selected"><%= _.escape( item.name ) %></option>
	<% } else { %>
			<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
	<% }
		});%>
		</select>
		<br>
		<select data-prop="worker">
			<option value=""> -- <?php _e('Worker','easy-appointments');?> -- </option>
	<% _.each(workers,function(item,key,list){
		  // create variables
		if(item.id == row.worker) { %>
			<option value="<%= item.id %>" selected="selected"><%= _.escape( item.name ) %></option>
	 <% } else { %>
			<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
	 <% }
		});%>
		</select>
	</td>
	<td colspan="2">
		<select data-prop="day_of_week" size="7" multiple>
	<% var weekdays = [
			{ id: "Monday", value: "Monday", name: "<?php _e('Monday','easy-appointments');?>"},
			{ id: "Tuesday", value: "Tuesday", name: "<?php _e('Tuesday','easy-appointments');?>"},
			{ id: "Wednesday", value: "Wednesday", name: "<?php _e('Wednesday','easy-appointments');?>"},
			{ id: "Thursday", value: "Thursday", name: "<?php _e('Thursday','easy-appointments');?>"},
			{ id: "Friday", value: "Friday", name: "<?php _e('Friday','easy-appointments');?>"},
			{ id: "Saturday", value: "Saturday", name: "<?php _e('Saturday','easy-appointments');?>"},
			{ id: "Sunday", value: "Sunday", name: "<?php _e('Sunday','easy-appointments');?>"}
		];
	  _.each(weekdays,function(item,key,list){
		// create variables
		if(_.indexOf(row.day_of_week, item.value) !== -1) { %>
			<option value="<%= item.value %>" selected="selected"><%= _.escape( item.name ) %></option>
	 <% } else { %>
			<option value="<%= item.value %>"><%= _.escape( item.name ) %></option>
	 <% }
	 });%>
		</select>
	</td>
	<td colspan="2">
		<strong><?php _e('Start', 'easy-appointments');?> :</strong><br>
		<input type="text" data-prop="time_from" class="time-from" value="<%= row.time_from %>"><br>
		<strong><?php _e('End', 'easy-appointments');?> :</strong><br>
		<input type="text" data-prop="time_to" class="time-to" value="<%= row.time_to %>">
	</td>
	<td colspan="2">
		<strong>&nbsp;</strong><br>
		<input type="text" data-prop="day_from" class="day-from" value="<%= row.day_from %>"><br>
		<strong>&nbsp;</strong><br>
		<input type="text" data-prop="day_to" class="day-to" value="<%= row.day_to %>">
	</td>
	<td>
		<select data-prop="is_working" name="">
			<% if(row.is_working == 0) { %>
			<option value="0" selected="selected"><?php _e('No', 'easy-appointments');?></option>
			<option value="1"><?php _e('Yes', 'easy-appointments');?></option>
			<% } else { %>
			<option value="0"><?php _e('No', 'easy-appointments');?></option>
			<option value="1" selected="selected"><?php _e('Yes', 'easy-appointments');?></option>
			<% } %>
		</select>
	</td>
	<td class="action-center">
		<button class="button button-primary btn-save"><?php _e('Save', 'easy-appointments');?></button>
		<button class="button btn-cancel"><?php _e('Cancel', 'easy-appointments');?></button>
	</td>
</script>

<script type="text/template" id="ea-tpl-connection-bulk">
	<div style="min-height: 380px; max-height: 380px;">
		<div class="step-1">
			<p class="bulk-text"><?php _e('Split groups', 'easy-appointments');?> <small>( <?php _e('each combination will be one connection', 'easy-appointments');?> )</small></p>
			<div class="bulk-row">
				<div class="bulk-field" style="width: 33%;">
					<label><?php _e('Locations','easy-appointments');?> :</label>
					<select data-prop="location" class="chosen-select" multiple>
						<% _.each(locations,function(item,key,list){ %>
						<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
						<% });%>
					</select>
				</div>
				<div class="bulk-field" style="width: 33%;">
					<label><?php _e('Services','easy-appointments');?> :</label>
					<select data-prop="service" class="chosen-select" multiple>
						<% _.each(services,function(item,key,list){ %>
						<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
						<% });%>
					</select>
				</div>
				<div class="bulk-field" style="width: 33%;">
					<label><?php _e('Workers','easy-appointments');?> :</label>
					<select data-prop="worker" class="chosen-select" multiple>
						<% _.each(workers,function(item,key,list){ %>
						<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
						<% });%>
					</select>
				</div>
			</div>
			<hr class="divider" />
			<p class="bulk-text"><?php _e('Shared values', 'easy-appointments');?> <small>( <?php _e('same for each combination', 'easy-appointments');?> )</small></p>
			<div class="bulk-row">
				<div class="bulk-field" style="width: 70%;">
					<label><?php _e('Days of week','easy-appointments');?></label>
					<select data-prop="day_of_week" size="7" multiple class="chosen-select">
						<% var weekdays = [
						{ id: "Monday", value: "Monday", name: "<?php _e('Monday','easy-appointments');?>"},
						{ id: "Tuesday", value: "Tuesday", name: "<?php _e('Tuesday','easy-appointments');?>"},
						{ id: "Wednesday", value: "Wednesday", name: "<?php _e('Wednesday','easy-appointments');?>"},
						{ id: "Thursday", value: "Thursday", name: "<?php _e('Thursday','easy-appointments');?>"},
						{ id: "Friday", value: "Friday", name: "<?php _e('Friday','easy-appointments');?>"},
						{ id: "Saturday", value: "Saturday", name: "<?php _e('Saturday','easy-appointments');?>"},
						{ id: "Sunday", value: "Sunday", name: "<?php _e('Sunday','easy-appointments');?>"}
						];
						_.each(weekdays,function(item,key,list){ %>
						<option value="<%= item.value %>"><%= _.escape( item.name ) %></option>
						<% });%>
					</select>
				</div>
				<div style="display: inline-flex; width: 30%;">
					<div class="bulk-field">
						<label><?php _e('Time from', 'easy-appointments');?> :</label>
						<input type="text" data-prop="time_from" class="time-from" value="<%= row.time_from %>"><br>

					</div>
					<div class="bulk-field">
						<label><?php _e('to', 'easy-appointments');?> :</label>
						<input type="text" data-prop="time_to" class="time-to" value="<%= row.time_to %>">
					</div>
				</div>
			</div>
			<div class="bulk-row">
				<div class="bulk-field" style="width: 15%;">
					<label><?php _e('Active from date', 'easy-appointments');?> :</label>
					<input type="text" data-prop="day_from" class="day-from" value="<%= row.day_from %>"><br>
				</div>
				<div class="bulk-field" style="width: 15%;">
					<label><?php _e('to date', 'easy-appointments');?> :</label>
					<input type="text" data-prop="day_to" class="day-to" value="<%= row.day_to %>">
				</div>
				<div class="bulk-field" style="width: 15%;">
					<label for=""><?php _e('Is Working', 'easy-appointments');?> :</label>
					<select data-prop="is_working" name="is_working">
						<% if(row.is_working == 0) { %>
						<option value="0" selected="selected"><?php _e('No', 'easy-appointments');?></option>
						<option value="1"><?php _e('Yes', 'easy-appointments');?></option>
						<% } else { %>
						<option value="0"><?php _e('No', 'easy-appointments');?></option>
						<option value="1" selected="selected"><?php _e('Yes', 'easy-appointments');?></option>
						<% } %>
					</select>
				</div>
			</div>
		</div>
		<div class="step-2" style="display: none; min-height: 380px; max-height: 380px; overflow-y: scroll;">
			<ul id="bulk-connections"></ul>
		</div>
	</div>
	<div class="bulk-footer">
		<button id="bulk-next" class="button-primary">Next</button>
		<button id="bulk-save" class="button-primary" disabled>Save connections ( <span id="bulk-connection-count">0</span> )</button>
	</div>
</script>

<script type="text/template" id="ea-tpl-single-bulk-connection">
	<li>
		<span class="bulk-value"><%= _.escape( _.findWhere(locations, {id:row.location}).name ) %></span>
		<span class="bulk-value"><%= _.escape( _.findWhere(services,  {id:row.service}).name ) %></span>
		<span class="bulk-value"><%= _.escape( _.findWhere(workers,   {id:row.worker}).name ) %></span>
		<span style="display: inline-block;"><button class="button bulk-connection-remove">Remove</button></span>
	</li>
</script>


<!--Customize -->
<script type="text/template" id="ea-tpl-custumize">
	<div class="wp-filter">
		<h2><?php _e('Connections', 'easy-appointments'); ?> :</h2>
		<table class="form-table form-table-translation">
			<tbody>
			<tr>
				<th class="row">
					<label for=""><?php _e('Multiple work', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="multiple.work" name="multiple.work" type="checkbox" <% if
					(_.findWhere(settings, {ea_key:'multiple.work'}).ea_value == "1") { %>checked<% } %>>
				</td>
				<td>
					<span class="description"> <?php _e('Mark this option if you want to calculate free worker slots only by current service and location. If it\'s not marked system will check if worker is working on any location and service at current time.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Compatibility mode', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="compatibility.mode" name="compatibility.mode" type="checkbox" <% if
					(_.findWhere(settings, {ea_key:'compatibility.mode'}).ea_value == "1") { %>checked<% } %>>
				</td>
				<td>
					<span class="description"> <?php _e('If you can\'t <strong>EDIT</strong> or <strong>DELETE</strong> conecntion or any other settings, you should mark this option. NOTE: <strong>After saving this options you must refresh page!</strong>', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Max number of appointments', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input style="width: 50px; margin-right: 20px;" class="field" data-key="max.appointments"
						   name="max.appointments" type="text"
						   value="<%= _.findWhere(settings, {ea_key:'max.appointments'}).ea_value %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('Number of appointments that one visitor can make reservation before limit alert is shown. Appointments are counted during one day.', 'easy-appointments'); ?></span>
				</td>
			</tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
			<tr>
				<th class="row">
					<label><?php _e('Auto reservation', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="pre.reservation" name="pre.reservation" type="checkbox" <% if
					(_.findWhere(settings, {ea_key:'pre.reservation'}).ea_value == "1") { %>checked<% } %>>
				</td>
				<td>
					<span class="description"> <?php _e('Make reservation at moment user select date and time!', 'easy-appointments'); ?></span>
				</td>
			</tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Turn nonce off', 'easy-appointments'); ?> :</label>
                </th>
                <td>
                    <input class="field" data-key="nonce.off" name="nonce.off" type="checkbox" <% if
                    (_.findWhere(settings, {ea_key:'nonce.off'}).ea_value == "1") { %>checked<% } %>>
                </td>
                <td>
                    <span class="description"> <?php _e('if you have issues with validation code that is expired in form you can turn off nonce but you are doing that on your own risk.', 'easy-appointments'); ?></span>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
			<tr>
				<th class="row">
					<label><?php _e('Default status', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<select class="field" name="ea-select-status" data-key="default.status">
						<option value="pending"
						<% if (_.findWhere(settings, {ea_key:'default.status'}).ea_value == "pending") {
						%>selected="selected"<% } %>><%= eaData.Status.pending %></option>
						<option value="confirmed"
						<% if (_.findWhere(settings, {ea_key:'default.status'}).ea_value == "confirmed") {
						%>selected="selected"<% } %>><%= eaData.Status.confirmed %></option>
					</select>
				</td>
				<td>
					<span class="description"> <?php _e('Default status of Appointment made by visitor.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
		<hr class="divider">
		<h2><?php _e('Mail', 'easy-appointments'); ?> : </h2>
		<h3><?php _e('Notifications', 'easy-appointments'); ?></h3>
		<p class="notifications-help"><?php _e('You can use this tags inside email content', 'easy-appointments'); ?> :
			<strong>#id#, #date#, #start#, #end#, #status#, #created#, #price#, #ip#, #link_confirm#, #link_cancel#, #url_confirm#,
				#url_cancel#, #service_name#, #service_duration#, #service_price#, #worker_name#, #worker_email#, #worker_phone#,
				#location_name#, #location_address#, #location_location#, <span id="custom-tags"></span></strong></p>
		<table class='notifications form-table'>
			<tbody>
			<tr>
				<td colspan="2">
					<p>
						<a class="mail-tab selected"
						   data-textarea="#mail-pending"><?php _e('Pending', 'easy-appointments'); ?></a>
						<a class="mail-tab"
						   data-textarea="#mail-reservation"><?php _e('Reservation', 'easy-appointments'); ?></a>
						<a class="mail-tab"
						   data-textarea="#mail-canceled"><?php _e('Canceled', 'easy-appointments'); ?></a>
						<a class="mail-tab"
						   data-textarea="#mail-confirmed"><?php _e('Confirmed', 'easy-appointments'); ?></a>
						<a class="mail-tab" data-textarea="#mail-admin"><?php _e('Admin', 'easy-appointments'); ?></a>
					</p>
					<textarea id="mail-template" style="height: 250px;" name="mail-template"><%= _.findWhere(settings, {ea_key:'mail.pending'}).ea_value %></textarea>
				</td>
			</tr>
				<tr style="display:none;">
					<td>
						<textarea id="mail-pending" class="field" data-key="mail.pending"><%= _.findWhere(settings, {ea_key:'mail.pending'}).ea_value %></textarea>
					</td>
					<td>
						<textarea id="mail-reservation" class="field" data-key="mail.reservation"><%= _.findWhere(settings, {ea_key:'mail.reservation'}).ea_value %></textarea>
					</td>
				</tr>
				<tr style="display:none;">
					<td>
						<textarea id="mail-canceled" class="field" data-key="mail.canceled"><%= _.findWhere(settings, {ea_key:'mail.canceled'}).ea_value %></textarea>
					</td>
					<td>
						<textarea id="mail-confirmed" class="field" data-key="mail.confirmed"><%= _.findWhere(settings, {ea_key:'mail.confirmed'}).ea_value %></textarea>
					</td>
				</tr>
				<tr style="display:none;">
					<td colspan="2">
						<textarea id="mail-admin" class="field" data-key="mail.admin"><%= (_.findWhere(settings, {ea_key:'mail.admin'}) != null) ? _.findWhere(settings, {ea_key:'mail.admin'}).ea_value: '' %></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<br>
		<table class="form-table form-table-translation">
			<tbody>
			<tr>
				<th class="row">
					<label for=""><?php _e('Pending notification emails', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input style="width: 300px" class="field" data-key="pending.email" name="pending.email" type="text"
						value="<%= _.findWhere(settings, {ea_key:'pending.email'}).ea_value %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('Enter email adress that will receive new reservation notification. Separate multiple emails with , (comma)', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Admin notification subject', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input style="width: 300px" class="field" data-key="pending.subject.email"
						name="pending.subject.email" type="text"
						value="<%= _.findWhere(settings, {ea_key:'pending.subject.email'}).ea_value %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('You can use any tag that is available as in custom email notifications.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Visitor notification subject', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input style="width: 300px" class="field" data-key="pending.subject.visitor.email"
						name="pending.subject.visitor.email" type="text"
						value="<%= _.findWhere(settings, {ea_key:'pending.subject.visitor.email'}).ea_value %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('You can use any tag that is available as in custom email notifications.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for="send.worker.email"><?php _e('Send email to worker', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="send.worker.email" name="send.worker.email" type="checkbox" <% if
					(_.findWhere(settings, {ea_key:'send.worker.email'}).ea_value == "1") { %>checked<% } %>><br>
				</td>
				<td>
					<span class="description"> <?php _e('Mark this option if you want to employee receive admin email after filing the form.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for="send.user.email"><?php _e('Send email to user', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="send.user.email" name="send.user.email" type="checkbox" <% if
					(_.findWhere(settings, {ea_key:'send.user.email'}).ea_value == "1") { %>checked<% } %>><br>
				</td>
				<td>
					<span class="description"> <?php _e('Mark this option if you want to user receive email after filing the form.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Send from', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input style="width: 300px" class="field" data-key="send.from.email" name="send.from.email"
						   type="text" value="<%= _.findWhere(settings, {ea_key:'send.from.email'}).ea_value %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('Send from email adress (Example: Name &lt;name@domain.com&gt;). Leave blank to use default address.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
		<hr class="divider">
		<h2><?php _e('Labels', 'easy-appointments'); ?> :</h2>
		<table class="form-table form-table-translation">
			<tbody>
			<tr>
				<th class="row">
					<label for=""><?php _e('Service', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="trans.service" name="service" type="text"
						   value="<%= _.escape( _.findWhere(settings, {ea_key:'trans.service'}).ea_value ) %>"><br>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Location', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="trans.location" name="location" type="text"
						   value="<%= _.escape( _.findWhere(settings, {ea_key:'trans.location'}).ea_value ) %>"><br>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Worker', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="trans.worker" name="worker" type="text"
						   value="<%= _.escape( _.findWhere(settings, {ea_key:'trans.worker'}).ea_value ) %>"><br>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Done message', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="trans.done_message" name="done_message" type="text"
						   value="<%= _.escape( _.findWhere(settings, {ea_key:'trans.done_message'}).ea_value ) %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('Message that user receive after completing appointment', 'easy-appointments'); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
		<hr class="divider">
		<h2><?php _e('Date & Time', 'easy-appointments'); ?> : </h2>
		<table class="form-table form-table-translation">
			<tbody>
			<tr>
				<th class="row">
					<label><?php _e('Time format', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<select data-key="time_format" class="field" name="time_format">
						<option value="00-24"
						<% if (_.findWhere(settings, {ea_key:'time_format'}).ea_value === "00-24") {
						%>selected="selected"<% } %>>00-24</option>
						<option value="am-pm"
						<% if (_.findWhere(settings, {ea_key:'time_format'}).ea_value === "am-pm") {
						%>selected="selected"<% } %>>AM-PM</option>
					</select>
				</td>
				<td>
					<span class="description"> <?php _e('Notice : date/time formating for email notification are done by <b>Settings > General</b>.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label><?php _e('Calendar localization', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<select data-key="datepicker" class="field" name="datepicker">
						<% var langs = [
						'af','ar','ar-DZ','az','be','bg','bs','ca','cs','cy-GB','da','de','el','en','en-AU','en-GB','en-NZ','en-US','eo','es','et','eu','fa','fi','fo','fr','fr-CA','fr-CH','gl','he','hi','hr','hu','hy','id','is','it','it-CH','ja','ka','kk','km','ko','ky','lb','lt','lv','mk','ml','ms','nb','nl','nl-BE','nn','no','pl','pt','pt-BR','rm','ro','ru','sk','sl','sq','sr','sr-SR','sv','ta','th','tj','tr','uk','vi','zh-CN','zh-HK','zh-TW'
						];
						_.each(langs,function(item,key,list){
						if(_.findWhere(settings, {ea_key:'datepicker'}).ea_value === item) { %>
						<option value="<%= item %>" selected="selected"><%= item %></option>
						<% } else { %>
						<option value="<%= item %>"><%= item %></option>
						<% }
						});%>
					</select>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label><?php _e('Block time', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="block.time" name="block.time" type="text"
						   value="<%= _.findWhere(settings, {ea_key:'block.time'}).ea_value %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('(in minutes). Prevent visitor from making an appointment if there are less minutes than this.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
		<hr class="divider">
		<h2><?php _e('Custom form fields', 'easy-appointments'); ?> -
			<small>Create all fields that you need. Custom order them by drag and drop.</small>
		</h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th colspan="2">
					<span>Name :</span>
					<input type="text">
					<span>Type :</span>
					<select>
						<option value="INPUT">Input</option>
						<option value="SELECT">Select</option>
						<option value="TEXTAREA">Textarea</option>
                        <option value="PHONE">Phone</option>
					</select>
					<button class="button button-primary btn-add-field"><?php _e('Add', 'easy-appointments'); ?></button>
				</th>
			</tr>
			<tr>
				<td colspan="2">
					<ul id="custom-fields"></ul>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<span class="description"> <?php _e('To use using the email notification for user there must be field named "email" or "e-mail"', 'easy-appointments'); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
		<hr class="divider">
		<h2>Form</h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th class="row">
					<label for=""><?php _e('Custom style', 'easy-appointments'); ?> :</label>
				</th>
				<td class="custom-css">
					<textarea class="field" data-key="custom.css"><% if (typeof _.findWhere(settings, {ea_key:'custom.css'}) !== 'undefined') { %><%= (_.findWhere(settings, {ea_key:'custom.css'})).ea_value %><% } %></textarea>
				</td>
				<td>
					<span class="description"> <?php _e('Place here custom css styles here. This will be included in both standard and bootstrap widget.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Turn off css files', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="css.off" name="css.off" type="checkbox" <% if (_.findWhere(settings,
					{ea_key:'css.off'}).ea_value == "1") { %>checked<% } %>><br>
				</td>
			</tr>
			</tbody>
		</table>
		<table class="form-table">
			<tbody>
			<tr>
				<th class="row">
					<label for=""><?php _e('I agree field', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" type="checkbox" name="show.iagree" data-key="show.iagree"<% if (typeof
					_.findWhere(settings, {ea_key:'show.iagree'}) !== 'undefined' && _.findWhere(settings,
					{ea_key:'show.iagree'}).ea_value == '1') { %>checked<% } %> />
				</td>
				<td>
					<span class="description"> <?php _e('I agree option at the end of form. If this is marked user must confirm "I agree" checkbox.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label><?php _e('After cancel go to', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<select data-key="cancel.scroll" class="field" name="cancel.scroll">
						<% var langs = [
						'calendar', 'worker', 'service', 'location'
						];
						_.each(langs,function(item,key,list){
						if(typeof _.findWhere(settings, {ea_key:'cancel.scroll'}) !== 'undefined' &&
						_.findWhere(settings, {ea_key:'cancel.scroll'}).ea_value === item) { %>
						<option value="<%= item %>" selected="selected"><%= item %></option>
						<% } else { %>
						<option value="<%= item %>"><%= item %></option>
						<% }
						});%>
					</select>
				</td>
			</tr>
			<tr>
				<th class="row go-to-page">
					<label for=""><?php _e('Go to page', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="submit.redirect" name="submit.redirect" type="text"
						   value="<%= _.findWhere(settings, {ea_key:'submit.redirect'}).ea_value %>"><br>
				</td>
				<td>
					<span class="description"> <?php _e('After a visitor creates an appointment on the front-end form. Leave blank to turn off redirect.', 'easy-appointments'); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
        <hr class="divider">
        <h2>GDPR</h2>
        <table class="form-table form-table-translation">
            <tbody>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Turn on checkbox', 'easy-appointments'); ?> :</label>
                    </th>
                    <td>
                        <input class="field" type="checkbox" name="gdpr.on" data-key="gdpr.on"<% if (typeof
                        _.findWhere(settings, {ea_key:'gdpr.on'}) !== 'undefined' && _.findWhere(settings,
                        {ea_key:'gdpr.on'}).ea_value == '1') { %>checked<% } %> />
                    </td>
                    <td>
                        <span class="description"> <?php _e('GDPR section checkbox.', 'easy-appointments'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Label', 'easy-appointments'); ?> :</label>
                    </th>
                    <td>
                        <input style="width: 300px" class="field" data-key="gdpr.label" name="gdpr.label" type="text"
                               value="<%= _.findWhere(settings, {ea_key:'gdpr.label'}).ea_value %>">
                    </td>
                    <td>
                        <span class="description"> <?php _e('Label next to checkbox.', 'easy-appointments'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Page with GDPR content', 'easy-appointments'); ?> :</label>
                    </th>
                    <td>
                        <input style="width: 300px" class="field" data-key="gdpr.link" name="gdpr.link" type="text"
                               value="<%= _.findWhere(settings, {ea_key:'gdpr.link'}).ea_value %>">
                    </td>
                    <td>
                        <span class="description"> <?php _e('Link to page with GDPR content.', 'easy-appointments'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Error message', 'easy-appointments'); ?> :</label>
                    </th>
                    <td>
                        <input style="width: 300px" class="field" data-key="gdpr.message" name="gdpr.message" type="text"
                               value="<%= _.findWhere(settings, {ea_key:'gdpr.message'}).ea_value %>">
                    </td>
                    <td>
                        <span class="description"> <?php _e('Message if user don\'t mark the GDPR checkbox.', 'easy-appointments'); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr class="divider">
		<h2>Money</h2>
		<table class="form-table form-table-translation">
			<tbody>
			<tr>
				<th class="row">
					<label for=""><?php _e('Currency', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="trans.currency" name="currency" type="text"
						   value="<%= _.findWhere(settings, {ea_key:'trans.currency'}).ea_value %>"><br>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Currency before price', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="currency.before" name="currency.before" type="checkbox" <% if
					(_.findWhere(settings, {ea_key:'currency.before'}).ea_value == "1") { %>checked<% } %>><br>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for=""><?php _e('Hide price', 'easy-appointments'); ?> :</label>
				</th>
				<td>
					<input class="field" data-key="price.hide" name="price.hide" type="checkbox" <% if
					(_.findWhere(settings, {ea_key:'price.hide'}).ea_value == "1") { %>checked<% } %>><br>
				</td>
			</tr>
			</tbody>
		</table>
		<br><br>
		<button class="button button-primary btn-save-settings"><?php _e('Save', 'easy-appointments'); ?></button>
		<br><br>
	</div>
</script>

<script type="text/template" id="ea-tpl-custom-forms">
    <li data-name="<%= _.escape(item.label) %>" style="display: list-item;">
        <div class="menu-item-bar">
            <div class="menu-item-handle">
                <span class="item-title"><span class="menu-item-title"><%= _.escape(item.label) %></span> <span
                            class="is-submenu" style="display: none;">sub item</span></span>
                <span class="item-controls">
                <span class="item-type"><%= item.type %></span>
                    <a class="single-field-options"><i class="fa fa-chevron-down"></i></a>
                </span>
            </div>
        </div>
    </li>
</script>

<script type="text/template" id="ea-tpl-custom-form-options">
<div class="field-settings">
    <p>
        <label>Label :</label><input type="text" class="field-label" name="field-label"
                                     value="<%= _.escape(item.label) %>">
    </p>
    <p>
        <label>Placeholder :</label><input type="text" class="field-mixed" name="field-mixed"
                                           value="<%= _.escape(item.mixed) %>">
    </p>
    <% if (item.type === "SELECT") { %>
    <p>
        <label>Options :</label>
    </p>
    <p>
    <ul class="select-options">
        <% _.each(item.options, function(element) { %>
        <li data-element="<%= element %>"><%= element %><a href="#" class="remove-select-option"><i
                        class="fa fa-trash-o"></i></a></li>
        <% }); %>
    </ul>
    </p>
    <p><input type="text"><a href="#" class="add-select-option">&nbsp;&nbsp;<i class="fa fa-plus"></i> Add option</a>
    </p>
    <% } %>
    <p>
        <label>Required :</label><input type="checkbox" class="required" name="required" <% if (item.required == "1") {
        %>checked<% } %>>
    </p>
    <p>
        <label>Visible :</label><input type="checkbox" class="visible" name="visible" <% if (item.visible == "1") {
        %>checked<% } %>>
    </p>
    <p><a href="#" class="deletion item-delete">Delete</a> | <a href="#" class="item-save">Apply</a></p>
</div>
</script>

<!-- TOOLS -->
<script type="text/template" id="ea-tpl-tools">
	<div class="wp-filter">
		<h2><?php _e('Test Email', 'easy-appointments');?></h2>
		<p><?php _e('Test if the mail service is working fine on this site by generating a test email that will be send to provided address.', 'easy-appointments');?></p>
		<table class="form-table form-table-translation">
			<tbody>
				<tr>
					<th class="row"><?php _e('To', 'easy-appointments');?></th>
					<td><input id="test-email-address" name="test-email-address" type="text" class="field" /> <span class="description"><?php _e('Email address', 'easy-appointments');?></span></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan>
						<button id="test-wp-mail" class="button button-primary"><?php _e('Send a Test Email', 'easy-appointments');?></button>
						<button id="test-mail" class="button button-primary"><?php _e('Send a Test Email (native)', 'easy-appointments');?></button>
					</td>
				</tr>
			</tbody>
		</table>
		<hr class="divider" />
		<h2><?php _e('Error log', 'easy-appointments'); ?></h2>
		<div style="text-align: center;">
			<textarea id="ea-error-log" style="font-family: monospace;width: 100%;min-height: 400px;"><?php _e('Loading...', 'easy-appointments'); ?></textarea>
		</div>
		<br/>
	</div>
</script>

<!-- TOOLS LOG -->
<script type="text/template" id="ea-tpl-tools-log">------------ ERROR #<%= item.id %> ------------
TYPE: <%= item.error_type %>
ERRORS: <%= item.errors %>
ERRORS_DATA: <%= item.errors_data %>
---------- ERROR #<%= item.id %> END ----------

</script>