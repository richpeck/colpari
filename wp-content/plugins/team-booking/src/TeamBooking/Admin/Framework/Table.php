<?php

namespace TeamBooking\Admin\Framework;

class Table implements Element
{
    protected $columns = array();
    protected $rows = array();
    protected $id = '';
    protected $class = '';
    protected $selectable_rows = FALSE;
    protected $no_sortable_columns = array();
    protected $filter_row = array();

    public function addColumns(array $array)
    {
        $this->columns = $array;
    }

    public function addRow(array $row)
    {
        $this->rows[] = $row;
    }

    public function addNoSortableColumn($column)
    {
        $this->no_sortable_columns[] = $column;
    }

    public function addToFilterRow($column, Element $element)
    {
        $this->filter_row[ $column ] = $element;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setSelectable($bool)
    {
        $this->selectable_rows = (bool)$bool;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function render()
    {
        echo '<table class="widefat';
        if (!empty($this->class)) echo ' ' . $this->class;
        echo '"';
        if (!empty($this->id)) echo ' id="' . $this->id . '"';
        echo '>';
        // Header
        echo '<thead><tr class="alternate">';
        if ($this->selectable_rows) {
            echo '<th>';
            echo '<input type="checkbox" style="margin:0" class="tb-table-select-all-rows no-sort"';
            echo '</th>';
        }
        $column_number = 0;
        foreach ($this->columns as $key => $column) {
            echo '<th style="font-weight:bold;"';
            if (in_array($column_number, $this->no_sortable_columns)) echo ' class="no-sort"';
            echo '>' . $column . '</th>';
            $column_number++;
        }
        echo '</tr>';
        echo '</thead>';
        // Data
        echo '<tbody>';
        foreach ($this->rows as $row) {
            echo '<tr';
            if (isset($row['class'])) echo ' class="' . $row['class'] . '"';
            echo '>';
            if ($this->selectable_rows) {
                echo '<td>';
                echo '<input type="checkbox" style="margin:0" class="tb-table-select-row" data-row="'
                    . (isset($row['data-row']) ? $row['data-row'] : '')
                    . '">';
                echo '</td>';
            }
            foreach ($this->columns as $number => $column) {
                echo '<td';
                if (isset($row['data-cell']) && isset($row['data-cell'][ $number ])) echo ' data-' . $row['data-cell'][ $number ]['data'] . '="' . $row['data-cell'][ $number ]['value'] . '"';
                echo '>';
                if ($row[ $number ] instanceof Element) {
                    $row[ $number ]->render();
                } elseif (is_array($row[ $number ])) {
                    foreach ($row[ $number ] as $item) {
                        if ($item instanceof Element) {
                            $item->render();
                        } else {
                            echo $item;
                        }
                    }

                } else {
                    echo $row[ $number ];
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';
        if (!empty($this->filter_row)) {
            echo '<tfoot><tr class="tbk-table-filter-row alternate ignore-row">';
            if ($this->selectable_rows) echo '<td></td>';
            foreach ($this->columns as $number => $column) {
                echo '<th>';
                if (isset($this->filter_row[ $number ])) {
                    $this->filter_row[ $number ]->render();
                }
                echo '</th>';
            }
            echo '</tr></tfoot>';
        }
        echo '</table>';
        if ($this->selectable_rows) {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $('#<?= $this->id ?>').on('change', '.tb-table-select-all-rows', function () {
                        if (this.checked) {
                            $('#<?= $this->id ?>').find('tr:visible').find('.tb-table-select-row').prop('checked', true).trigger('change');
                        } else {
                            $('#<?= $this->id ?>').find('tr:visible').find('.tb-table-select-row').prop('checked', false).trigger('change');
                        }
                    });
                });
            </script>
            <?php
        }
    }
}