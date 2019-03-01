<div id="icon-edit" class="icon32"><br />
</div>
<div class="rns-report-wrapper">
	<div class="rns-front-page-main-heading">
		<h1>
        	<strong><?php echo __( 'Awesome Support Reports & Statistics', 'reports-and-statistics' ); ?>
            </strong>
        </h1>
	</div>
	<div class="rns-full-width">
		<div class="rns-front-page-header-report">
			<h2><?php echo __( 'All Tickets: Quick Summary By Status', 'reports-and-statistics' ); ?></h2>
		</div>
		<ul class="rns-tickets-ul">
			<?php 
				if ( ! empty( $ticketCountReport ) ) {
					$non_color_class = '';
					foreach ( $ticketCountReport as $label=>$status ) {
						$bgColor = rns_get_ticket_color_by_status( $status['status'], $status['slug'] );
						if ( $bgColor == '' ) {
							$non_color_class = ' class = " rns_non_colored_status " ';
						} 
			?>
                        <li style="border-style: solid; border-width: 1px; border-left-width:2px; border-color: #EEEEEE; border-left-color: <?php echo $bgColor .';' ; ?>" <?php echo  $non_color_class ; ?> > 
                            <a href="edit.php?post_type=ticket&post_status=<?php echo $status['status']; ?>">
                                <label style="color: #9F9FA0;"><?php echo ucwords( $label ); ?>&nbsp;(<?php echo $status['count']; ?>)</label>
                            </a>
                        </li>
			<?php
					}
				}
			?>
                        <li style="background-color:<?php echo $bgColor = rns_get_ticket_color_by_status( 'closed', 'closed' ); ?>;"> 
                            <a href="edit.php?post_type=ticket&post_status=all&wpas_status=closed">
                                <label><?php echo __( 'Closed Tickets', 'reports-and-statistics' ); ?>:</label>
                                <span><?php echo $closedTickets; ?></span> 
                            </a>
                        </li>
		</ul>
		
		<?php
		/************************************************************************
		* Start of display of system/general reports
		************************************************************************/
		?>
		
		<div class="rns-front-page-header-report rns-top-border">
			<h2><?php echo __( 'Core Reports', 'reports-and-statistics' ); ?></h2>
			<p class="rns-front-page-subtitle"><?php echo __( 'You can slice, dice and dissect these reports to create hundreds of custom reports.', 'reports-and-statistics' ); ?></p>
		</div>
		
		<ul class="rns-report-ul">
			<li> 
				<a href="?post_type=ticket&page=wpas-reports&action=basic_report">
					<label><?php echo __( 'Ticket Counts', 'reports-and-statistics' ); ?></label>
				</a> 
				<div class="rns-saved-reports-small-text"> <br /> <br /> <?php echo __('A barchart and table showing a count of tickets by status.','reports-and-statistics' ); ?> </div>				
			</li>

			<li> 
				<a href="?post_type=ticket&page=wpas-reports&action=reply_report">
					<label><?php echo __( 'Productivity Analysis', 'reports-and-statistics' ); ?></label>
				</a>
				<div class="rns-saved-reports-small-text"> <br /> <br /> <?php echo __('A series of barcharts and tables that show the average/median/maximum number of replies in a set of tickets.','reports-and-statistics' ); ?> </div>				
			</li>
			<li> 
				<a href="?post_type=ticket&page=wpas-reports&action=resolution_report">
					<label><?php echo __( 'Resolution Analysis', 'reports-and-statistics' ); ?></label>
				</a>
				<div class="rns-saved-reports-small-text"> <br /> <br /> <?php echo __('A barchart and table that displays the average time it takes to close a ticket.','reports-and-statistics' ); ?> </div>				
			</li>
			<li> 
				<a href="?post_type=ticket&page=wpas-reports&action=delay_report">
					<label><?php echo __( 'Delay analysis', 'reports-and-statistics' ); ?></label>
				</a> 
				<div class="rns-saved-reports-small-text"> <br /> <br /> <?php echo __('A barchart and table that show the average time it takes to issue the first reply on a ticket.','reports-and-statistics' ); ?> </div>				
			</li>
			<li> 
				<a href="?post_type=ticket&page=wpas-reports&action=distribution_report">
					<label><?php echo __( 'Distribution Analysis', 'reports-and-statistics' ); ?></label>
				</a> 
				<div class="rns-saved-reports-small-text"> <br /> <br /> <?php echo __('A barchart and table that show the number of tickets with various reply counts. Use this to see how much activity it takes to close out a ticket.','reports-and-statistics' ); ?> </div>				
			</li>
            <li> 
				<a href="?post_type=ticket&page=wpas-reports&action=trend_report&state=both">
					<label><?php echo __( 'Trend  Analysis', 'reports-and-statistics' ); ?></label>
				</a> 
				<div class="rns-saved-reports-small-text"> <br /> <br /> <?php echo __('A barchart and table showing a the number of tickets opened in the last 7 days/weeks/months. Very useful when filtered for just closed tickets as well.','reports-and-statistics' ); ?> </div>									
			</li>
		</ul>

		<?php
		/************************************************************************
		* Start of display of shared and saved reports
		************************************************************************/
		?>
		
        <?php
			$user_role = rns_get_current_user_role();		
		?>
        
		<div class="rns-front-page-header-report rns-top-border">
			<h2><?php echo __( 'My Saved Reports', 'reports-and-statistics' ); ?></h2>
		</div>
		
		<ul class="rns-saved-reports">
        	<?php 
				$own_reports =	rns_get_save_report_list( get_current_user_id() , "" );	
				rns_list_report_from_array( $own_reports );
			?>
		</ul>
		
		<div class="rns-front-page-header-report top-border">
			<h2><?php echo __( 'Shared Reports (Reports shared by other team members)', 'reports-and-statistics' ); ?></h2>
		</div>
		
		<ul class="rns-saved-reports">
        	<?php 
				$others_reports =	rns_get_save_report_list( get_current_user_id(), $user_role );	
				rns_list_report_from_array( $others_reports ); 
			?>
		</ul>		
      
	</div>
</div>
