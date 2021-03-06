        <section class="general admin">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active">Edit Hotel Group: <?= $group->name ?></li>
                    </ol>
                </nav>
                <div class="container w-50 mt-5 mx-auto">
                <?php if(isset($errors) && $errors) { ?>
                    <div class="alert alert-danger" role="alert">
                        <ul>
                        <?php foreach($errors as $error) { ?>
                            <li><?= $error ?></li>
                        <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
                    <form action="/admin/hotel-group/update/<?=$group->id?>?success=<?= urlencode('/admin') ?>&error=<?= urlencode('/admin/hotel-group/update/' . $group->id) ?>" method="POST">
                        <div class="form-group">
                            <label for="name">Hotel group name: <span class="text-danger" title="This field is required">*</span></label>
                            <input type="text" autofocus class="form-control" maxlength="42" required id="name" name="name" value="<?= $group->name ?>" />
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <label for="street">Address street: <span class="text-danger" title="This field is required">*</span></label>
                                <input type="text" class="form-control" maxlength="42" required id="street" name="street" value="<?= $group->address['street'] ?>" />
                            </div>
                            <div class="col">
                                <label for="number">Address number: <span class="text-danger" title="This field is required">*</span></label>
                                <input type="number" class="form-control" min="1" max="9999" required id="number" name="number" value="<?= $group->address['number'] ?>" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <label for="city">City: <span class="text-danger" title="This field is required">*</span></label>
                                <input type="text" class="form-control" maxlength="42" required id="city" name="city" value="<?= $group->address['city'] ?>" />
                            </div>
                            <div class="col">
                                <label for="postal">Postal code: <span class="text-danger" title="This field is required">*</span></label>
                                <input type="number" class="form-control" min="10000" max="999999" required id="postal" name="postal_code" value="<?= $group->address['postal_code'] ?>" />
                            </div>
                        </div>
                        <div class="form-group phones">
                            <label>Phone numbers:</label>
                        <?php foreach($group->phone_numbers as $phone) { ?>
                            <div class="input-group phone">
                                <input type="number" class="form-control" min="2100000000" max="6999999999" name="phones[]" value="<?= $phone ?>" />
                                <div class="input-group-append">
                                    <button class="remove-input btn btn-secondary"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        <?php } ?>
                            <div class="input-group phone">
                                <input type="number" class="form-control" min="2100000000" max="6999999999" name="phones[]" />
                                <div class="input-group-append">
                                    <button class="add-input btn btn-primary"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group emails">
                            <label>Email addresses:</label>
                        <?php foreach($group->email_addresses as $email) { ?>
                            <div class="input-group email">
                                <input type="email" class="form-control" name="emails[]" value="<?= $email ?>" />
                                <div class="input-group-append">
                                    <button class="remove-input btn btn-secondary"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        <?php } ?>
                            <div class="input-group email">
                                <input type="email" class="form-control" name="emails[]" />
                                <div class="input-group-append">
                                    <button class="add-input btn btn-primary"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-primary" name="submit" value="Update" />
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
                btn = event.target.closest(".remove-input")
                if(btn !== null) {
                    event.preventDefault()
                    let el = btn.parentNode.parentNode
                    el.parentNode.removeChild(el)
                }
            })
        </script>
    ';
