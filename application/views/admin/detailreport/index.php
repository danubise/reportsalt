<div class="row">
	<div class="col-md-12 form-inline">
		<div class="form-group">
			<button type="submit" class="btn btn-primary" id="btnLeft"><</button>
		</div>
		<div class="form-group">
			<select id="year" class="form-control" name="year">
				<? for ($i = $this->config->system->startyear; $i <= date('Y'); $i++) { ?>
					<option value="<?=$i?>"><?=$i?></option>
				<? } ?>
			</select>
		</div>
		<div class="form-group">
			<select id="month" class="form-control" name="month">
				<? foreach (get_month() AS $key => $moth) { ?>
					<option value="<?=($key + 1)?>"><?=$moth?></option>
				<? } ?>
			</select>
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-primary" id="btnRight">></button>
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-success" id="getReport">Сформировать</button>
		</div>
	</div>
	<div class="col-md-12">
		<div id="report"></div>
	</div>
</div>