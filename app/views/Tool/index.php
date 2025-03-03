            <div class="text-center">
                <h5>Create new section</h5>
                <p class="font-weight-light text-info">Autocreate new section</p>
            </div>
            <div class="text-center">
                <form action="/?c=Tool&a=createsection" method="post">
                    <div class="form-group">
                        <label for="section_name">Enter section name: </label>
                        <input type="text" id="section_name" name="section_name" placeholder="newsectionname" required>
                        <p class="text-warning"><em>*Allowed only unicode characters and numbers. Use dash or underscore to generate calmmelcase. (no spaces, no special chars etc.).</em></p>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>