

<!-- Styles -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<!-- Or for RTL support -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
<form action="php/add_DEtournament.php" method="POST">
    <label for="teams"></label>
    <div id="select3-container">
        <select class="form-select" name="teams[]" id="multiple-select-field" data-placeholder="Teams kiezen" multiple>
            <option value="1">A</option>
            <option value="2">B</option>
            <option value="3">C</option>
            <option value="4">D</option>
        </select>
    </div>
    <br>
    <input class="buttonsubmitteams" type="submit" value="Maak toernooi">

</form>

<style>
    .select2-container {
        width: 40%;
        margin-left: 30%;
    }
    .select3-container {
        width: 40%;
        margin-left: 30%;
    }
</style>

<script>
    $( '#multiple-select-field' ).select2( {
        theme: "bootstrap-5",
        placeholder: $( this ).data( 'placeholder' ),
        closeOnSelect: false,
    } );


</script>