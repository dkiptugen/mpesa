<div class="col-md-6  col-md-offset-3  col-xs-12">
    <form action="" method="post" class="form form-horizontal" role="form">
        <div class="form-group">
            <label for="trans-id" class="control-label col-md-2">Transaction ID</label>
            <div class="col-md-8">
                <input type="text" name="transid" id="trans-id" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="amount" class="control-label col-md-2">Amount</label>
            <div class="col-md-8">
                <input type="text" name="amount" id="amount" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="receiver" class="control-label col-md-2">Receiver</label>
            <div class="col-md-8">
                <input type="text" name="receiver" id="receiver" class="form-control">
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
            <button type="submit" class="btn btn-success pull-right">Submit</button>
        </div>
    </form>
</div>