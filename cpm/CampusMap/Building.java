
/** Java class "Buildings.java" generated from Poseidon for UML.
 *  Poseidon for UML is developed by <A HREF="http://www.gentleware.com">Gentleware</A>.
 *  Generated with <A HREF="http://jakarta.apache.org/velocity/">velocity</A> template engine.
 */
package CampusMap;

import java.awt.event.MouseEvent;
import processing.core.PApplet;
import java.net.URLEncoder;
//import java.io.UnsupportedEncodingException;

//import processing.core.*;
//import saito.objloader.*;

/**
 * Constructor
 */
public class Building extends ObjectOfInterest {
	
	final static Color	gray		= new Color(100, 100, 100, 255);
	final static Color	red			= new Color(255, 0, 0, 255);
	
	final static float	roomAlpha	= 0.5f;
	
	public	CollisionSphere collisionSpheres[];
	//private int currentDetailLevel;
	private	boolean		mouseOver;
	public	FVector		entrancePosition;
	public	FVector		roomCoordOrigin;
	public	float		roomCoordRotation = 0;
	public	float		flyAroundRadius = 0;
	public	float		flyAroundCenterHeight = 0;
	public	boolean		onScreen;
	private	boolean		forceSelectedModel = false;
	
	private	FVector		showPosition = null;
	private float		sinusVal = 0.0f;
	public	String		myBuildingNo = "";
	public	String		shortDescription;
	public	String		longDescription;
	
	
	public Building(CampusMap drawApplet, FVector myPos, FVector myScale, FVector myRot, String[] modelsToLoad, String address, boolean drawLines, boolean zFade)
	{
		super(drawApplet, myPos, myScale, myRot, modelsToLoad, address, true, drawLines, zFade);
		myParentApplet.env.objectInitDisplay.setText("Buildings");
		drawApplet.registerMouseEvent( this );
		//test spheres
		//drawApplet.registerDraw( this );
		mouseOver = false;
		onScreen = false;
		entrancePosition = new FVector(3);
		roomCoordOrigin = new FVector(3);
		shortDescription = "";
		longDescription  = "";
	}
	
	// methods to change or obtain the level of detail
	public void setDetailLevel(int p_detailLevel){
		currentDetailLevel = p_detailLevel;
	}

	public void changeModelDetail(){
		System.out.print("Modelchange");
	}
	
	public void initColSpheresArray(int i) {
		collisionSpheres = new CollisionSphere[i];
	}
	
	public void addColSphere(int i, FVector vPosition, float fRadius) {
		if (i<collisionSpheres.length)
			collisionSpheres[i] = new CollisionSphere(vPosition, fRadius);
	}
	
	public CollisionSphere testColSpheresWithPoint(FVector point, float distance) {
		for (int i = 0; i < collisionSpheres.length; i++) {
			CollisionSphere sphere = collisionSpheres[i].testPoint(point, distance);
			if (sphere != null) return sphere;
		}
		return null;
	}
	
	private boolean testColSpheresWithMouse() {
		if (onScreen) {
			FVector mouseRay = myParentApplet.theCamera.getMousePointRay();
			FVector camerPos = myParentApplet.theCamera.getPos();
			myModels[currentDetailLevel].setLineColor(gray);
			if (myParentApplet.controls.getThreeDeeControlEnabled()) {
				for (int i = 0; i < collisionSpheres.length; i++) {
					if (collisionSpheres[i].testRay(camerPos, mouseRay)) {
						mouseOver = true;
						myModels[currentDetailLevel].setLineColor(red);
						Environment.setToolTip(shortDescription, 0);
						return true;
					}
				}
			}
			if (mouseOver)
				Environment.clearToolTip();
			mouseOver = false;
		}
		return false;
	}
	
	private boolean testColSpheresWithFrustum() {
		FVector cameraLeft = myParentApplet.theCamera.getLeft();
		FVector cameraUp = myParentApplet.theCamera.getUp();
		for (int i = 0; i < collisionSpheres.length; i++) {
			for (int m = -1; m < 2; m++) {
				if (isPointInFrustum(collisionSpheres[i].getPosition().add(cameraLeft.multiply(m*collisionSpheres[i].getRadius())))) {
					return true;
				}
				if (isPointInFrustum(collisionSpheres[i].getPosition().add(cameraUp.multiply(m*collisionSpheres[i].getRadius())))) {
					return true;
				}
			}
		}
		return false;
	}
	
	private boolean isPointInFrustum(FVector point) {
		float screenX = myParentApplet.screenX(point.e[0], point.e[1], point.e[2]);
		if (screenX < 0 || screenX > myParentApplet.width) return false;
		float screenY = myParentApplet.screenY(point.e[0], point.e[1], point.e[2]);
		if (screenY < 0 || screenY > myParentApplet.height) return false;
		return true;
	}
		
	public void mouseEvent(MouseEvent e) {
		switch (e.getID()) {
	    case MouseEvent.MOUSE_MOVED:
	    	if (onScreen)
	    		testColSpheresWithMouse();
	    	break;
	    case MouseEvent.MOUSE_CLICKED:
	    	if (mouseOver && myParentApplet.controls.getThreeDeeControlEnabled()) {
	    		FVector		centerPosition	=  getCenterPosition();
				centerPosition.setZ(flyAroundCenterHeight);
				myParentApplet.theCamera.flyToRoomInBuilding(entrancePosition, centerPosition, flyAroundRadius, 2000, 2000, this, false);
				String header = new String("");
				String content = new String("");
//				try {
					header = URLEncoder.encode(shortDescription);
					content = URLEncoder.encode(longDescription);
//				} catch (UnsupportedEncodingException excep) {}
				Environment.setToolTip(" Beenden des Detailmodus durch Button unten.", 4);
				myParentApplet.setTouring(true);
				Environment.setBrowserUrl(Environment.phpRoot + "index.php?javatemplate=dummy&headline=" + header + "&content=" + content);
	    	}
	    	break;
		}
	}
	
	public FVector getCenterPosition() {
		return myPos.multiply(myParentApplet.getBuildingUniformScale());
	}
	
	//debug
	public void draw(CampusMap myDrawApplet){
		if (onScreen && drawingActive) {
			//draw room position or alike inside model
			if (showPosition!=null) {
				sinusVal+=0.2f;
				if (sinusVal > Math.PI) sinusVal -= Math.PI;
				myDrawApplet.pushMatrix();
				myDrawApplet.scale(1/myDrawApplet.getBuildingUniformScale());
				myDrawApplet.translate(showPosition.getX(), showPosition.getY(), showPosition.getZ());
				myDrawApplet.noStroke();
				myDrawApplet.fill(255, 30, 30, 255);
				myDrawApplet.sphere(8);
				myDrawApplet.fill(255, 30, 30, 140);
				myDrawApplet.sphere(8 + (float)Math.sin(sinusVal)*8);
				myDrawApplet.popMatrix();
			}
			//draw model
			super.draw(myDrawApplet);
		}
		
		FVector cameraPosFloatArray=new FVector(0,0,0);
		FVector distanceVector = new FVector(0,0,0);
		int distance=0;

		// calculating the need of detail-level-changing
		if(selectable==true){
			
			cameraPosFloatArray = ((CampusMap)myParentApplet).theCamera.getPos();
			distanceVector = cameraPosFloatArray.subtract(myPos.multiply( ((CampusMap)myDrawApplet).getBuildingUniformScale()) );
			distance = (int)distanceVector.magnitude();
			if(distance<=CLOSER_DISTANCE){ 
				setDetailLevel(CLOSER);
			}else setDetailLevel(OVERVIEW);
			
			if (forceSelectedModel)
				setDetailLevel(SELECTED);
			if (currentDetailLevel == SELECTED && !(modelsLoaded[SELECTED]))
				setDetailLevel(CLOSER);
			if (currentDetailLevel == CLOSER && !(modelsLoaded[CLOSER]))
				setDetailLevel(OVERVIEW);

		}
		if(myModels[0].getZFade())
			myModels[0].setFadeMidPoint(new float[]{cameraPosFloatArray.getX(),
													cameraPosFloatArray.getY(),
													0});
	}
	
	public void debugDraw(CampusMap myDrawApplet) {
		myDrawApplet.hint(PApplet.DISABLE_DEPTH_TEST);
		for (int i = 0; i < collisionSpheres.length; i++) {
			if (mouseOver)
				collisionSpheres[i].debugDraw(myDrawApplet, myParentApplet.color(100, 255, 100));
			else
				collisionSpheres[i].debugDraw(myDrawApplet);
		}
		myDrawApplet.noHint(PApplet.DISABLE_DEPTH_TEST);
	}
	
	public void testIfOnScreen() {
		onScreen  = testColSpheresWithFrustum();
	}
	
	
	public FVector convertDatabasePos(FVector databasePos) { 
		FVector returnVec = databasePos.subtract(roomCoordOrigin.multiply(myParentApplet.getBuildingUniformScale())).rotateZ(PApplet.radians(-roomCoordRotation)).rotateX(PApplet.PI).multiply(myParentApplet.getBuildingUniformScale()*myParentApplet.getBuildingDatabaseScale());
		//returnVec.printMe("returnVec");
		return  returnVec;
	}
	
	public void showPositionInBuilding(FVector pos) {
		sinusVal = 0.0f;
		myAlpha = roomAlpha;
		showPosition = pos;
	}
	
	public void setForceSelectedModel(boolean status) {
		forceSelectedModel = status;
	}
	
	public void clearPositionInBuilding() {
		myAlpha = 1.0f;
		showPosition = null;
	}
	
 } // end Buildings



