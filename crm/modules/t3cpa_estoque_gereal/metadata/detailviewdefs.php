<?php
$module_name = 't3cpa_estoque_gereal';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
        ),
      ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'cod_prod',
            'label' => 'LBL_COD_PROD',
          ),
          1 => 
          array (
            'name' => 'eg_nm_produto',
            'label' => 'LBL_EG_NM_PRODUTO',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'eg_lote',
            'label' => 'LBL_EG_LOTE',
          ),
          1 => 
          array (
            'name' => 'eg_saldo',
            'label' => 'LBL_EG_SALDO',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 't3cpa_estoqcretarias_name',
          ),
          1 => '',
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 't3cpa_estoqregionais_name',
          ),
        ),
      ),
    ),
  ),
);
?>
