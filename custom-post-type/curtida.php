<?php 

function registrar_cpt_curtida() {
  register_post_type('curtida', array(
    'label' => 'Curtida',
    'description' => 'Curtida',
    'public' => true,
    'show_io' => true,
    'capability_type' => 'post',
    'rewrite' => array('slug' => 'curtida', 'with_front' => true),
    'query_var' => true,
    'supports' => array('custom-fields', 'author', 'title'),
    'publicly-queryable' => true
  ));
}

add_action('init', 'registrar_cpt_curtida');

?>
