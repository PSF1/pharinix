<div class="container-fluid">
    <div class="row"  id="menu">
        <div id="mainMenu" type="nav" class="col-md-12">
            {if isset($block.mainMenu)}{$block.mainMenu}{/if}
        </div>
    </div>
    <div class="row"  id="content">
        <div id="colLeft" class="col-md-3 topSpace">
            {if isset($block.colLeft)}{$block.colLeft}{/if}
        </div>
        <div id="colRight" class="col-md-9 topSpace">
            {if isset($block.colRight)}{$block.colRight}{/if}
        </div>
    </div>
    <div class="row"  id="footer">
        <div id="col1" type="nav" class="col-sm-12">
            <div class="row"  id="foot1">
                <div id="footCopy" class="col-md-12">
                    {if isset($block.footCopy)}{$block.footCopy}{/if}
                </div>
            </div>
        </div>
    </div>
</div>
