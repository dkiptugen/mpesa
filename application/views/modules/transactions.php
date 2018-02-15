<div class="col-md-6  col-md-offset-3  col-xs-12">
    <form action="" method="post" class="form form-horizontal" role="form">
        <div class="form-group">
            <label for="trans-id" class="control-label col-md-2">Transaction ID</label>
            <div class="col-md-8">
                <input type="text" name="transid" id="trans-id" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="conv" class="control-label col-md-2">Conversion ID</label>
            <div class="col-md-8">
                <input type="text" name="convid" id="conv" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="party-a" class="control-label col-md-2">Party A</label>
            <div class="col-md-8">
                <input type="text" name="partya" id="party-a" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="itype" class="control-label col-md-2">Identifier Type</label>
            <div class="col-md-8">
                <select type="text" name="identifier" id="itype" class="form-control" aria-required="true">
                    <option value="">Enter...</option>
                    <option value="1">MSISDN</option>
                    <option value="4">SHORTCODE</option>
                    <option value="2">TILL NUMBER</option>
                </select>
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