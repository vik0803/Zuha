<div class="userGroups form">
<?php echo $this->Form->create('UserGroup');?>
	<fieldset>
 		<legend><?php echo __('Edit User Group'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('title');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'User Groups',
		'items' => array(
			$this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('UserGroup.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('UserGroup.id'))),
			$this->Html->link(__('List User Groups', true), array('action' => 'index')),
			)
		),
	)));
?>