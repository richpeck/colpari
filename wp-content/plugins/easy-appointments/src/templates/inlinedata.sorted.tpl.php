<script>
    window.eaData = {};
    var ea = window.eaData;
    ea.Locations = <?php echo $this->models->get_pre_cache_json('ea_locations', $this->models->get_order_by_part('ea_locations')); ?>;
    ea.Services = <?php echo $this->models->get_pre_cache_json('ea_services', $this->models->get_order_by_part('ea_services')); ?>;
    ea.Workers = <?php echo $this->models->get_pre_cache_json('ea_staff', $this->models->get_order_by_part('ea_workers')); ?>;
    ea.MetaFields = <?php echo $this->models->get_pre_cache_json('ea_meta_fields', array('position' => 'ASC')); ?>;
    ea.Status = <?php echo json_encode($this->logic->getStatus()); ?>
</script>