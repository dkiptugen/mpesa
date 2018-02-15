<div class="col-md-6  col-md-offset-3  col-xs-12">
    <form action="" method="post" class="form form-horizontal" role="form">
        <div class="form-group">
            <label for="command-id" class="control-label col-md-2">Payment Type</label>
            <div class="col-md-8">
                <select type="text" name="commandid" id="command-id" class="form-control" aria-required="true">
                    <option value="">Enter...</option>
                    <option value="BusinessPayBill">PayBill</option>
                    <option value="BusinessBuyGoods">Buy Goods</option>
                    <option value="DisburseFundsToBusiness">DisburseFundsToBusiness</option>
                    <option value="BusinessToBusinessTransfer">BusinessToBusinessTransfer</option>
                    <option value="MerchantToMerchantTransfer">MerchantToMerchantTransfer</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="sender" class="control-label col-md-2">Sender</label>
            <div class="col-md-8">
                <input type="text" name="sender" id="sender" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="stype" class="control-label col-md-2">Sender Type</label>
            <div class="col-md-8">
                <select type="text" name="s_identifier" id="stype" class="form-control" aria-required="true">
                    <option value="">Enter...</option>
                    <option value="1">MSISDN</option>
                    <option value="4">SHORTCODE</option>
                    <option value="2">TILL NUMBER</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="receiver" class="control-label col-md-2">Receiver</label>
            <div class="col-md-8">
                <input type="text" name="receiver" id="receiver" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="rtype" class="control-label col-md-2">Receiver Type</label>
            <div class="col-md-8">
                <select type="text" name="r_identifier" id="rtype" class="form-control" aria-required="true">
                    <option value="">Enter...</option>
                    <option value="1">MSISDN</option>
                    <option value="4">SHORTCODE</option>
                    <option value="2">TILL NUMBER</option>
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
            <label for="acref" class="control-label col-md-2">Account Reference</label>
            <div class="col-md-8">
                <input type="text" name="acref" id="acref" class="form-control">
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