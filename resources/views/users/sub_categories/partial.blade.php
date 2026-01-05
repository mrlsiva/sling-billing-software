<form class="row" id="addSubCategory">
	@csrf
	<div class="modal-body">

		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label for="name" class="form-label">Upload Sub Category Image</label>
					<div class="input-group">
						<input type="file" name="image" id="image" class="form-control" accept="image/*">
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Category</label>
					<select class="form-control" name="category" id="category" required="">
						<option value=""> Select </option>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Sub Category</label>
					<input type="text" id="sub_category" name="sub_category" class="form-control" required="">
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-primary" id="subCategorySubmit">Submit</button>
	</div>
</form>