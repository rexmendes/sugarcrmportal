<?php
$dashletData['ant_producaoDashlet']['searchFields'] = array (
  'prod_lote' => 
  array (
    'default' => '',
  ),
  'prod_qtd' => 
  array (
    'default' => '',
  ),
  'prod_validade' => 
  array (
    'default' => '',
  ),
  'ant_producat_produto_name' => 
  array (
    'default' => '',
  ),
);
$dashletData['ant_producaoDashlet']['columns'] = array (
  'ant_producat_produto_name' => 
  array (
    'type' => 'relate',
    'link' => 'ant_producao_ant_produto',
    'label' => 'LBL_ANT_PRODUCAO_ANT_PRODUTO_FROM_ANT_PRODUTO_TITLE',
    'width' => '10%',
    'default' => true,
    'name' => 'ant_producat_produto_name',
  ),
  'prod_lote' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_PROD_LOTE',
    'width' => '10%',
    'default' => true,
    'name' => 'prod_lote',
  ),
  'date_modified' => 
  array (
    'width' => '15%',
    'label' => 'LBL_DATE_MODIFIED',
    'name' => 'date_modified',
    'default' => false,
  ),
  'created_by' => 
  array (
    'width' => '8%',
    'label' => 'LBL_CREATED',
    'name' => 'created_by',
    'default' => false,
  ),
  'assigned_user_name' => 
  array (
    'width' => '8%',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'name' => 'assigned_user_name',
    'default' => false,
  ),
);
