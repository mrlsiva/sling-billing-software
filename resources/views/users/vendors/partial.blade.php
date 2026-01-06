<form class="row"  id="addVendor">
	@csrf
	<div class="modal-body">

		<div class="row">
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Name</label>
					<input type="text" id="name" name="name" class="form-control" required="" placeholder="Enter Name">
				</div>
			</div>
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Phone</label>
					<input type="text" id="phone" name="phone" class="form-control" required="" placeholder="Enter Phone">
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Email</label>
					<input type="email" id="email" name="email" class="form-control" placeholder="Enter Email">
				</div>
			</div>
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">GST</label>
					<input type="text" id="gst" name="gst" class="form-control" placeholder="Enter GST number">
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Address</label>
					<input type="text" id="address" name="address" class="form-control" placeholder="Enter Address">
				</div>
			</div>
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Address1</label>
					<input type="text" id="address1" name="address1" class="form-control" placeholder="Enter Alternate Address">
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">City</label>
					<input type="text" id="city" name="city" class="form-control" placeholder="Enter City">
				</div>
			</div>
			<div class="col-md-6">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">State</label>
					<input type="text" id="state" name="state" class="form-control" placeholder="Enter State">
				</div>
			</div>
		</div>

	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-primary" id="addVendorButton">Submit</button>
	</div>
</form>