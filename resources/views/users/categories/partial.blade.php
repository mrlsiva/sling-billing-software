<form class="row" id="addCategory">
	@csrf
	<div class="modal-body">

		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label for="name" class="form-label">Upload Category Image</label>
					<div class="input-group">
						<input type="file" name="image" id="image" class="form-control" accept="image/*">
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Category Name</label>
					<input type="text" id="category" name="category" class="form-control" required="">
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-primary" id="categorySubmit">Submit</button>
	</div>
</form>