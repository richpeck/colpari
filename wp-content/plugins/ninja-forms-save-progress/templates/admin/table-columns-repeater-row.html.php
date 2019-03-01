<script id="tmpl-nf-save-progress-table-columns-repeater-row" type="text/template">

    <!-- Draggable Handle for re-ordering rows -->
    <div>
        <span class="dashicons dashicons-menu handle"></span>
    </div>

    <!-- Columns: Field (Select) -->
    <div class="nf-select">
        <# try { #>
            {{{ data.renderFieldSelect( 'field', data.field ) }}}
        <# } catch ( err ) { #>
            <input type="text" class="setting" value="{{ data.field }}" data-id="field" >
        <# } #>
    </div>

    <!-- Dismissible icon for removing a row -->
    <div>
        <span class="dashicons dashicons-dismiss nf-delete"></span>
    </div>

</script>