
<div class="container">

	<?php echo validation_errors(); ?>

    <div class="row">

		<?php 

			echo form_open_multipart('google/compose', array( "name" => "gmailcompose" ));

			echo form_fieldset('Compose Email');

	 		echo "<div class='form-group'>";
			echo form_label( 'From', 'from' );
			echo "&nbsp;";
			echo form_input( 'from', $this->system->get_option( 'google_email_from' ), array( 'class' => 'form-control' ) );
			echo "</div>";

			echo "<div class='form-group'>";
			echo form_label( 'To', 'to' );
			echo "&nbsp;";
			echo form_input( 'to', '', array( 'class' => 'form-control' ) );
			echo "</div>";

			echo "<div class='form-group'>";
			echo form_label( 'Subject', 'subject' );
			echo "&nbsp;";
			echo form_input( 'subject', '', array( 'class' => 'form-control' ) );
			echo "</div>";

			echo "<div class='form-group'>";
			echo form_label( 'Message', 'message' );
			echo "&nbsp;";
			echo form_textarea( 'message', '', array( 'class' => 'form-control' ) );
			echo "</div>";

			echo "<div class='form-group'>";
			echo form_label( 'Attachment', 'file' );
			echo "&nbsp;";
			echo form_upload( 'file', '', array( 'class' => 'form-control' ) );
			echo "</div>";
			 
			echo form_submit('submit', 'Send', array( 'class' => 'btn btn-default' ) );

			echo form_fieldset_close();

			echo form_close();

		 ?>

	</div>

</div>