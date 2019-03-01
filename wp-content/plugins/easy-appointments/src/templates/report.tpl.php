<script type="text/template" id="ea-report-main">
<div class="report-container">
	<ul id="tab-header" class="reports">
		<li class="report" data-report="overview">
			<a>
				<i class="fa fa-table"></i>
				<span><?php _e('Time table', 'easy-appointments');?></span>
			</a>
		</li>
		<li class="report" data-report="money">
			<a class="disabled">
				<i class="fa fa-money"></i>
				<span><?php _e('Money', 'easy-appointments');?></span>
			</a>
		</li>
		<li class="report" data-report="excel">
			<a>
				<i class="fa fa-file-excel-o"></i>
				<span><?php _e('Export', 'easy-appointments');?></span>
			</a>
		</li>
	</ul>
	<div id="report-content">
		<div class="report-message"><?php _e('Click on menu icon to open report.', 'easy-appointments');?><br> <?php _e('New reports are comming soon!', 'easy-appointments');?></div>
	</div>
</div>
</script>

<!-- template for overview report -->
<script type="text/template" id="ea-report-overview">
	<div class="filter-select">
		<label htmlFor=""><?php _e('Location', 'easy-appointments');?> :</label>
		<select name="location" id="overview-location">
			<option value="">-</option>
			<% _.each(cache.Locations,function(item,key,list){ %>
				<option value="<%= item.id %>"><%= item.name %></option>
			<% });%>
		</select>
		<label htmlFor=""><?php _e('Service', 'easy-appointments');?> :</label>
		<select name="service" id="overview-service">
			<option value="">-</option>
			<% _.each(cache.Services,function(item,key,list){ %>
				<option value="<%= item.id %>"><%= item.name %></option>
			<% });%>
		</select>
		<label htmlFor=""><?php _e('Worker', 'easy-appointments');?> :</label>
		<select name="worker" id="overview-worker">
			<option value="">-</option>
			<% _.each(cache.Workers,function(item,key,list){ %>
				<option value="<%= item.id %>"><%= item.name %></option>
			<% });%>
		</select>
		<span>&nbsp&nbsp;</span>
		<button class="refresh button-primary"><?php _e('Refresh', 'easy-appointments');?></button><br><br>
		<div name="month" class="datepicker" id="overview-month"/><br>
	</div>
	<div id="overview-data">
</div>
</script>

<!-- Template for overview report -->
<script type="text/template" id="ea-report-excel">
<div>
    <div>
        <a id="ea-export-customize-columns-toggle" href="#"><?php _e('Customize columns for export!','easy-appointments');?></a>
        <div id="ea-export-customize-columns" style="display: none;">
            <p>Columns: <b><?php echo implode(', ', $this->models->get_all_tags_for_template());?></b></p>
            <?php _e('Place fields separate by , for example: id,name,email','easy-appointments');?>
            <p><input id="ea-export-custom-columns" type="text" style="width:800px" value="<?php echo get_option('ea_excel_columns', '');?>" /></p>
            <button id="ea-export-save-custom-columns" class="btn"><?php _e('Save settings', 'easy-appointments');?></button>
        </div>
    </div>
    <div>&nbsp;</div>
    <form id="ea-export-form" action="<%= export_link %>" method="get">
        <input type="hidden" name="action" value="ea_export">
        <?php _e('From','easy-appointments');?> : <input class="ea-datepicker" type="text" name="ea-export-from"> <?php _e('To','easy-appointments');?> : <input class="ea-datepicker" type="text" name="ea-export-to">
        <p><?php _e('Export data to CSV, can be imported to MS Excel, OpenOffice Calc... ', 'easy-appointments');?></p>
        <button class="eadownloadcsv button-primary"><?php _e('Export data', 'easy-appointments');?></button>
    </form>
</div>
</script>