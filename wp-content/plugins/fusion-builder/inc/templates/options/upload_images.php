<div class="fusion-multiple-upload-images">
	<input
		type="hidden"
		name="{{ param.param_name }}"
		id="{{ param.param_name }}"
		class="fusion-multi-image-input"
		value="{{ option_value }}"
	/>
	<input
		type='button'
		class='button-upload fusion-builder-upload-button'
		value='{{ fusionBuilderText.select_images }}'
		data-type="image"
		data-title="{{ fusionBuilderText.select_images }}"
		data-id="fusion-multiple-images"
		data-element="{{ param.element }}"
	/>
	<div class="fusion-multiple-image-container">
	</div>
</div>
