<div class="col-md-3">
	<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#putBalance">Пополнить баланс</button>
	<label class="description"><i style="opacity: 0.5">Через Interkassa</i></label>
</div>

<!-- Modal -->
<div class="modal fade" id="putBalance" tabindex="-1" role="dialog" aria-labelledby="putBalanceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        	<form action="<?=$payFormAction?>" method="POST">
	            <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                <h4 class="modal-title" id="putBalanceLabel">Пополнение счёта</h4>
	            </div>
	            <div class="modal-body">
	            	<div class='row'>
	            		<div class='col-lg-6'>
	            			Введите необходимую сумму
	            		</div>
	            		<div class='col-lg-6'>
	               			<?=$payFormFields?> 
	            		</div>
	            	</div>
	            </div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
	                <button type="submit" class="btn btn-primary">Оплатить</button>
	            </div>
        	</form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->