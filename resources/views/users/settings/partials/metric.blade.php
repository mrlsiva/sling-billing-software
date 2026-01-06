<form class="row" id="addMetric">
	@csrf
	<div class="modal-body">

		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label for="choices-single-groups" class="form-label text-muted">Metric</label>
					<input type="text" id="name" name="name" class="form-control" required="">
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-primary" id="addMetricButton">Submit</button>
	</div>
</form>