<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Test three.js</title>
	<style>
		body {margin: 0;overflow: hidden;}
		canvas{width: 100%; height: 100%;}
	</style>
	<link rel="stylesheet" href="css/jquery.modal.min.css">
</head>
<body>
	
	<div id="container"></div>
	<div id="ex1" class="modal">
		<a href="#" rel="modal:close"></a>
	</div>
	<script src="jquery/jquery.min.js"></script>
	<script src="jquery/jquery.modal.min.js"></script>
	<script src="three.js"></script>
	<script src="js/renderers/Projector.js"></script>
	<script src="js/renderers/CanvasRenderer.js"></script>
	<script>


		let camera, scene, renderer
		let texture_placeholder,
		isUserInteracting = false,
		onMouseDownMouseX = 0, onMouseDownMouseY = 0,
		onPointerDownPointerX = 0, onPointerDownPointerY = 0, 
		onPointerDownLon = 0, onPointerDownLat = 0,
		lon = 90, onMouseDownLon = 0,
		lat = 0, onMouseDownLat = 0,
		phi = 0, theta = 0,
		target = new THREE.Vector3(),
		objects = new THREE.Object3D() ,geomObject,
		// mouse event variable
		mouse = new THREE.Vector2(),
		raycaster = new THREE.Raycaster()
		
		let loadTexture = (path) => {
			let texture = new THREE.Texture(texture_placeholder)
			let material = new THREE.MeshBasicMaterial({ map: texture, overdraw:0.5})

			let image = new Image()
			image.onload = function() {
				texture.image = this
				texture.needsUpdate = true
			}
			image.src = path

			return material
		}

		let init = () => {
			let container, mesh
			container = document.getElementById('container')
			camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 1, 10000)
			scene = new THREE.Scene()

			texture_placeholder = document.createElement('canvas')
			texture_placeholder.width = 128
			texture_placeholder.height = 128

			let context = texture_placeholder.getContext('2d')
			context.fillStyle = 'rgb(200,200,200)'
			context.fillRect(0, 0, texture_placeholder.width, texture_placeholder.height)
			
			createSky()
			createClickObject()

			renderer = new THREE.CanvasRenderer()
			renderer.setPixelRatio(window.devicePixelRatio)
			renderer.setSize(window.innerWidth, window.innerHeight)
			container.appendChild(renderer.domElement)
			
			document.addEventListener('mousedown', onDocumentMouseDown, false)
			document.addEventListener('mousemove', onDocumentMouseMove, false)
			document.addEventListener('mouseup', onDocumentMouseUp, false)
			document.addEventListener('wheel', onDocumentMouseWheel, false)
			document.addEventListener('touchstart', onDocumentTouchStart, false)
			document.addEventListener('touchmove', onDocumentTouchMove, false)
			window.addEventListener('resize', onWindowResize, false)
		}
		
		let createSky = () => {
			let materials = [
							loadTexture( 'textures/skybox/px.jpg'),
							loadTexture( 'textures/skybox/nx.jpg'),
							loadTexture( 'textures/skybox/py.jpg'),
							loadTexture( 'textures/skybox/ny.jpg'),
							loadTexture( 'textures/skybox/pz.jpg'),
							loadTexture( 'textures/skybox/nz.jpg')
			]

			let geometry = new THREE.BoxBufferGeometry(300, 300, 300, 17, 17, 17)
			geometry.scale(-1, 1, 1)
			mesh = new THREE.Mesh(geometry, materials)
			scene.add(mesh)
		}

		let createClickObject = () => {
			let geometry = new THREE.SphereBufferGeometry(2,10,10)
			for (let index = 0; index < 10; index++) {
				let object = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({color: Math.random() * 0xffffff, opacity:0.5}))
				object.position.x = Math.random() * 70 - 10
				object.position.y = 0
				object.position.z = Math.random() * 70 - 10

				object.rotation.x = Math.random() * 2 * Math.PI
				// object.rotation.y = Math.random() * 2 * Math.PI
				object.rotation.z = Math.random() * 2 * Math.PI
				
				// object.scale.x = Math.random() * 2 + 1
				// object.scale.y = Math.random() * 2 + 1
				// object.scale.z = Math.random() * 2 + 1
				object.castShadow = true
				object.receiveShadow = true
				object.userData = 'Gambar ' + index
				objects.add(object)
			}
			scene.add(objects)
		}

		let onWindowResize = () => {
			camera.aspect = window.innerWidth / window.innerHeight
			camera.updateProjectionMatrix()

			renderer.setSize(window.innerWidth, window.innerHeight)
		}

		
		let onDocumentMouseDown = (event) => {
			event.preventDefault()
			isUserInteracting = true
			onPointerDownPointerX = event.clientX
			onPointerDownPointerY = event.clientY

			onPointerDownLon = lon
			onPointerDownLat = lat

			mouse.x = (event.clientX / window.innerWidth) * 2 - 1
			mouse.y = -(event.clientY / window.innerHeight) * 2 + 1		
			
			raycaster.setFromCamera(mouse, camera)

			let intersect = raycaster.intersectObjects(objects.children)
			// for (let index = 0; index < intersect.length; index++) {
			// 	intersect[index].object.material.color.set(0xffffff)
			// }
			// console.log(intersect[0])
			if(intersect.length > 0) {
				$("p").remove()
				$("<p>"+intersect[0].object.userData+"</p>").appendTo("#ex1"),
				$('#ex1').modal({
					fadeDuration:250, fadeDelay:1.4,
					})
			}
		}

		let onDocumentMouseMove = (event) => {
			if(isUserInteracting === true) {
				lon = (onPointerDownPointerX - event.clientX) * 0.1 + onPointerDownLon
				lat = (event.clientY - onPointerDownPointerY) * 0.1 + onPointerDownLat
			}
		}

		let onDocumentMouseUp = (event) => {
			isUserInteracting = false
		}

		let onDocumentMouseWheel = (event) => {
			let fov = camera.fov + event.deltaY * 0.05
			camera.fov = THREE.Math.clamp(fov, 10, 75)
			camera.updateProjectionMatrix()
		}

		let onDocumentTouchStart = (event) => {
			if(event.touches.length == 1) {
				event.preventDefault()
				onPointerDownPointerX = event.touches[0]. pageX
				onPointerDownPointerY = event.touches[0]. pageY

				onPointerDownLon = lon
				onPointerDownLat = lat
			}
		}

		let onDocumentTouchMove = (event) => {
			if ( event.touches.length == 1 ) {

				event.preventDefault();

				lon = ( onPointerDownPointerX - event.touches[0].pageX ) * 0.1 + onPointerDownLon;
				lat = ( event.touches[0].pageY - onPointerDownPointerY ) * 0.1 + onPointerDownLat;

			}
		}


		let animate = () => {
			requestAnimationFrame( animate )
			update()
		}

		let update = () => {
			if(isUserInteracting === false) {
				lon +=0.1
			}

			lat = Math.max( -85, Math.min(85, lat))
			phi = THREE.Math.degToRad(90 - lat)
			theta = THREE.Math.degToRad( lon );

			target.x = 500 * Math.sin( phi ) * Math.cos( theta );
			target.y = 500 * Math.cos( phi );
			target.z = 500 * Math.sin( phi ) * Math.sin( theta );
			
			camera.lookAt( target );
			
			renderer.render( scene, camera );
		}
		

		init()
		animate()

	</script>
</body>
</html>