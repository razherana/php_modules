# CheckInput - No more ifs and returns

## How to use ?

- Just use the helper function : check_input()
- Add the data to check in arg1
- Add the rules in arg2
- Maybe add custom messages rules in arg3?
  (eg: `['parameter_name:condition_name' => 'Message here']`)
- Maybe use custom access_method rules in arg4?

### Adding more rules or messages

- You can take template of config.php
- You can add more check methods in user_config.php
- The syntax in `checking` is pretty simple,
  `"cond_name" => (callable:bool) or ([callable:bool, "/regex to get vars/"])`
- The syntax of `messages` is actually the same, just returns a string. Or true if you want to continue after getting the false on the `checking` (eg: optional)
