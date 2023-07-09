<?php 

function registrar_cpt_produto() {
  register_post_type('produto', array(
    'label' => 'Produto',
    'description' => 'Produto',
    'public' => true,
    'show_io' => true,
    'capability_type' => 'post',
    'rewrite' => array('slug' => 'produto', 'with_front' => true),
    'query_var' => true,
    'supports' => array('custom-fields', 'author', 'title'),
    'publicly-queryable' => true
  ));
}

add_action('init', 'registrar_cpt_produto');

?>