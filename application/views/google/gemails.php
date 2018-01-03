
<div class="container">


    <div class="row">

        <div class="col-md-11 col-center-align">
            <br><br>

            <button type="button" class="fetchGemails pull-right">Fetch emails</button>

            <h1>Google Emails</h1>

            <br>
            <br>

            <div class="list-emails">

                <table id="gemails" class="display table table-striped" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th># ID</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message ID</th>
                            <th>Thread ID</th>
                            <th>Created At</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th># ID</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message ID</th>
                            <th>Thread ID</th>
                            <th>Created At</th>
                            <th>Last Updated</th>
                        </tr>
                    </tfoot>
                </table>


            </div>

        </div><!-- col-center -->

    </div><!-- row -->

</div>

<script type="text/javascript">
    $(document).ready(function() {

        var gemailTable = $('#gemails').DataTable( {
            "processing": true,
            "serverSide": true,
            "ajax": "/google/emaillists"
        } );

        $(".fetchGemails").click(function(e) {
            $.post('/google/get_emails', function( data ) {
                gemailTable.ajax.reload();
                console.log(data);
            });
        });

    } );
</script>