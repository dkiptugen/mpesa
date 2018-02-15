<div class="col-md-6  col-md-offset-3  col-xs-12">
    <form action="" method="post" class="form form-horizontal" role="form">
        <div class="form-group">
            <label for="command-id" class="control-label col-md-2">Payment Type</label>
            <div class="col-md-8">
                <select type="text" name="commandid" id="command-id" class="form-control" aria-required="true">
                    <option value="">Enter...</option>
                    <option value="SalaryPayment">Salary</option>
                    <option value="BusinessPayment">Business</option>
                    <option value="PromotionPayment">Promotion</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="amount" class="control-label col-md-2">Amount</label>
            <div class="col-md-8">
                <input type="text" name="amount" id="amount" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="occassion" class="control-label col-md-2">Ocassion</label>
            <div class="col-md-8">
                <input type="text" name="occassion" id="occassion" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="occassion" class="control-label col-md-2">Ocassion</label>
            <div class="col-md-8">
                <input type="text" name="occassion" id="occassion" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="remarks" class="control-label col-md-2">Remarks</label>
            <div class="col-md-8">
                <textarea name="remarks" id="remarks" class="form-control">
                </textarea>
            </div>
        </div>
        <div class="form-group col-md-10">
            <div class="pull-left">
                <?=$msg; ?>
            </div>
            <button type="submit" class="btn btn-success pull-right">Send Payment</button>
        </div>
    </form>
</div>