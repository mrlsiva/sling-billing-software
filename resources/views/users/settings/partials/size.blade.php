<form class="row" id="addSize">
	@csrf
	<div class="modal-body">

		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Size</label>
					<input type="text" id="name" name="name" class="form-control" required="">
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-primary" id="addSizeButton">Submit</button>
	</div>
</form>