<?php defined('ABSPATH') or exit; ?>

<tr>
    <th>
        <?php echo $this->settings['name']; ?>
    </th>
    <td>
        <table class="as-fa-options-table">
            <tr>
                <?php foreach ( $this->settings['options']['views'] as $label => $view_name ): ?>
                    <td>

                        <div>
                            <strong>
                                <?php echo $view_name; ?>
                            </strong>
                        </div>

                        <?php foreach( $this->settings['options']['positions'][$label] as $key => $name  ): ?>
                            <div>
                                <label>
                                    <?php $active = isset($values[$this->settings['field']][$label][$key]) ? true : false; ?>
                                    <input type="checkbox" name="wpas_<?php echo $this->settings['id']; ?>[<?php echo $this->settings['field']; ?>][<?php echo $label; ?>][<?php echo $key; ?>]" value="1" <?php checked( $active ); ?>>
                                    <?php echo $name; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>
    </td>
</tr>


        