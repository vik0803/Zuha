<div class="users view row">
	<div class="col-md-2">
		<?php echo $this->element('Galleries.thumb', array('model' => 'User', 'foreignKey' => $user['User']['id'], 'thumbSize' => 'medium')); ?>
	</div>
	<div class="col-md-10">
	    <h2><?php echo $user['User']['first_name'] . ' ' . $user['User']['last_name'] ?></h2>
	    <?php echo $this->element('Webpages.menus', array('id' => $user['UserRole']['name'] . '-dashboard')); ?>
	</div>
</div>

<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Users',
		'items' => array(
			$this->Html->link(__('Edit'), array('action' => 'edit', $user['User']['id'])),
			$this->Html->link(__('Delete'), array('action' => 'delete', $user['User']['id']), array(), 'Are you sure you want to delete ' . $user['User']['full_name']),
			),
		),
	)));
