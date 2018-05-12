        <section class="general admin">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active">Add Hotel Group</li>
                    </ol>
                </nav>
                <div class="container w-50 mt-5 mx-auto">
                    <form action="/admin/hotel-group/createSubmit" method="POST">
                        <div class="form-group">
                            <label for="name">Hotel group name:</label>
                            <input type="text" autofocus class="form-control" maxlength="42" required id="name" name="name" />
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <label for="street">Address street:</label>
                                <input type="text" class="form-control" maxlength="42" required id="street" name="street" />
                            </div>
                            <div class="col">
                                <label for="number">Address number:</label>
                                <input type="number" class="form-control" min="1" max="9999" required id="number" name="number" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <label for="city">City:</label>
                                <input type="text" class="form-control" maxlength="42" required id="city" name="city" />
                            </div>
                            <div class="col">
                                <label for="postal">Postal code:</label>
                                <input type="number" class="form-control" min="10000" max="999999" required id="postal" name="postal_code" />
                            </div>
                        </div>
                        <div class="form-group phones">
                            <label>Phone numbers:</label>
                            <div class="input-group phone">
                                <input type="number" class="form-control" name="phones[]" />
                                <div class="input-group-append">
                                    <button class="add-input btn btn-primary"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group emails">
                            <label>Email addresses:</label>
                            <div class="input-group email">
                                <input type="email" class="form-control" name="emails[]" />
                                <div class="input-group-append">
                                    <button class="add-input btn btn-primary"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-primary" name="submit" value="Add" />
                    </form>
                </div>
            </div>            
        </section>
<?php
    $block['scripts'] = '
        <script>
            document.addEventListener("click", event => {
                let btn = event.target.closest(".add-input")
                if(btn !== null) {
                    event.preventDefault()
                    let el = btn.parentNode.parentNode
                    const newEl = el.parentNode.insertBefore(el.cloneNode(true), null)
                    let input = newEl.querySelector("input")
                    input.value = ""
                    input.focus()
                    el.removeChild(btn.parentNode)
                }
            })
        </script>
    ';