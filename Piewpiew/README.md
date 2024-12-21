# Piewpiew - Php view

## How to use ?

- Modify config.php and remove the line who throws exception

### Using it

- Just use piewpiew() function to use your views with your favorite compiler.
  You'll probably use this more...
- Use piewpiew_view() function to get the ViewElement

### Adding more compilers

- Make a folder in the compilers folder
- Add the Compiler class in there and it extends AbstractCompiler
- Add components to the Compiler, these are classes that extends Component.
  Each component handles parts of code you want to change, like ```:: $test ::```
  and changes '::' to echo.
- Maybe add a custom ViewVars class, it handles all elements of the view...
  You may want to add methods for your compiled parts!
- You can take example of the `HTMLCompiler`, have fun!
