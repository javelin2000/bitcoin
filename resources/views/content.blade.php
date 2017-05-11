@extends('layouts.template')
@section('main')
<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
    <h1 class="page-header">Main</h1>

    <div class="row placeholders">
        <div class="col-xs-6 col-sm-3 placeholder">
            <p>
                <form class="form-inline">
                    <h4>indexing by:</h4>
                    <select onchange="location=value" type="text"  name="input_search" class="form-control"
                            required size = "1" name = "name[]">
                        <option disabled >select...</option>
                        <option >itemID</option>
                        <option>description</option>
                    </select>
                </form>
            </p>
        </div>

    </div>
<h2 class="sub-header">Section title</h2>
</div>
@show
