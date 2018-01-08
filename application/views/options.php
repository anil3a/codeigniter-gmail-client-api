
<div class="container">

	<?php echo validation_errors(); ?>

    <div class="row">

		<?php 

			echo form_open('home/settings',array('name' => 'options' ) );

			echo form_fieldset('Setting values');

			foreach ( $options as $value)
			{
		 		echo "<div class='form-group'>";
				echo form_label( $value['name'], $value['name'] );
				echo ( $value['active'] == 0 ) ? ' <span class="text-danger">(Disabled)</span>' : '';
				echo "&nbsp;";
				echo form_input( $value['name'], $value['value'], array( 'class' => 'form-control' ) );
				echo "</div>";
			}
			 
			echo form_submit('submit', 'Submit', array( 'class' => 'btn btn-default' ) );

			echo form_fieldset_close();

			echo form_close();

		 ?>

	</div>

</div>