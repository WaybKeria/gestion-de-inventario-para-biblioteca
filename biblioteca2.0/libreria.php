
<?php
session_start(); // Iniciar la sesión

class Usuario {
    private $nombre;
    private $librosPrestados = [];

    public function __construct($nombre) {
        $this->nombre = $nombre;
    }

    public function prestarLibro(Libro $libro) {
        if ($libro->prestar()) {
            $this->librosPrestados[] = $libro;
            return true;
        } else {
            return false;
        }
    }

    public function devolverLibro(Libro $libro) {
        $libro->devolver();
        $key = array_search($libro, $this->librosPrestados);
        if ($key !== false) {
            unset($this->librosPrestados[$key]);
        }
    }

    public function mostrarLibrosPrestados() {
        echo "Libros prestados a {$this->nombre}:<br>";
        foreach ($this->librosPrestados as $libro) {
            echo "- {$libro->getTitulo()}<br>";
        }
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getLibrosPrestados() {
        return $this->librosPrestados;
    }
}

class Libro {
    private $titulo;
    private $autor;
    private $disponible;

    public function __construct($titulo, $autor) {
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->disponible = true;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function estaDisponible() {
        return $this->disponible;
    }

    public function prestar() {
        if ($this->disponible) {
            $this->disponible = false;
            return true;
        } else {
            return false;
        }
    }

    public function devolver() {
        $this->disponible = true;
    }
}

// Arreglo de usuarios almacenado en la variable de sesión
if (!isset($_SESSION['usuarios'])) {
    $_SESSION['usuarios'] = [];
}

$usuarios = $_SESSION['usuarios'];

// Arreglo de libros en la biblioteca
$biblioteca = [
    new Libro("El señor de los anillos", "J.R.R. Tolkien"),
    new Libro("Cien años de soledad", "Gabriel García Márquez"),
];

// Verificar si se envió un formulario para agregar usuario
if (isset($_POST['agregarUsuario'])) {
    $nombreUsuario = $_POST['nombreUsuario'];
    $usuarios[] = new Usuario($nombreUsuario);
    $_SESSION['usuarios'] = $usuarios; // Actualizar la variable de sesión
}

// Verificar si se envió un formulario para prestar un libro
if (isset($_POST['prestarLibro'])) {
    $nombreUsuario = $_POST['usuario'];
    $tituloLibro = $_POST['tituloLibro'];
    
    // Buscar el usuario
    $usuarioEncontrado = null;
    foreach ($usuarios as $usuario) {
        if ($usuario->getNombre() === $nombreUsuario) {
            $usuarioEncontrado = $usuario;
            break;
        }
    }
    
    if ($usuarioEncontrado) {
        // Buscar el libro
        $libroEncontrado = null;
        foreach ($biblioteca as $libro) {
            if ($libro->getTitulo() === $tituloLibro) {
                $libroEncontrado = $libro;
                break;
            }
        }
        
        if ($libroEncontrado) {
            // Prestar el libro al usuario
            if ($usuarioEncontrado->prestarLibro($libroEncontrado)) {
                echo "Libro prestado con éxito a {$nombreUsuario}: {$tituloLibro}";
            } else {
                echo "El libro no está disponible para préstamo.";
            }
        } else {
            echo "El libro no se encontró en la biblioteca.";
        }
    } else {
        echo "El usuario no se encontró.";
    }
}

// Verificar si se envió un formulario para devolver un libro
if (isset($_POST['devolverLibro'])) {
    $nombreUsuario = $_POST['usuario'];
    $tituloLibro = $_POST['tituloLibro'];
    
    // Buscar el usuario
    $usuarioEncontrado = null;
    foreach ($usuarios as $usuario) {
        if ($usuario->getNombre() === $nombreUsuario) {
            $usuarioEncontrado = $usuario;
            break;
        }
    }
    
    if ($usuarioEncontrado) {
        // Buscar el libro en los libros prestados al usuario
        foreach ($usuarioEncontrado->getLibrosPrestados() as $libro) {
            if ($libro->getTitulo() === $tituloLibro) {
                // Devolver el libro
                $usuarioEncontrado->devolverLibro($libro);
                echo "Libro devuelto con éxito por {$nombreUsuario}: {$tituloLibro}";
                break;
            }
        }
    } else {
        echo "El usuario no se encontró.";
    }
}

?>
<h2>Prestar Libro</h2>
<form method="post">
    <label for="usuario">Usuario:</label>
    <select name="usuario" required>
        <?php foreach ($usuarios as $usuario): ?>
            <option value="<?php echo $usuario->getNombre(); ?>"><?php echo $usuario->getNombre(); ?></option>
        <?php endforeach; ?>
    </select>
    <label for="tituloLibro">Título del Libro:</label>
    <select name="tituloLibro" required>
        <?php foreach ($biblioteca as $libro): ?>
            <option value="<?php echo $libro->getTitulo(); ?>"><?php echo $libro->getTitulo(); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" name="prestarLibro" value="Prestar Libro">
</form>

<h2>Devolver Libro</h2>
<form method="post">
    <label for="usuario">Usuario:</label>
    <select name="usuario" required>
        <?php foreach ($usuarios as $usuario): ?>
            <option value="<?php echo $usuario->getNombre(); ?>"><?php echo $usuario->getNombre(); ?></option>
        <?php endforeach; ?>
    </select>
    <label for="tituloLibro">Título del Libro:</label>
    <select name="tituloLibro" required>
        <?php foreach ($biblioteca as $libro): ?>
            <option value="<?php echo $libro->getTitulo(); ?>"><?php echo $libro->getTitulo(); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" name="devolverLibro" value="Devolver Libro">
</form>

<h2>Libros Prestados</h2>
<?php foreach ($usuarios as $usuario): ?>
    <h3><?php echo $usuario->getNombre(); ?></h3>
    <?php $librosPrestados = $usuario->getLibrosPrestados(); ?>
    <?php if (!empty($librosPrestados)): ?>
        <ul>
            <?php foreach ($librosPrestados as $libro): ?>
                <li><?php echo $libro->getTitulo(); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay libros prestados.</p>
    <?php endif; ?>
<?php endforeach; ?>
<link rel="stylesheet" href="style.css">
</body>
</html>
