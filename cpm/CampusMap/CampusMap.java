/*
 * Created on 30.05.2005
 *
 * @author David
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

package CampusMap;

import processing.core.*;
import objLoader.OBJModel;
import java.util.Vector;
import java.awt.image.PixelGrabber;
//import processing.opengl.*;

import javax.swing.border.LineBorder;

public class CampusMap extends PApplet{

	// DEFINITIONS
	static	final	float	TOL 			= 0.00001f;

	static	final	int	SCREEN_WIDTH		= 570;
	static	final	int	SCREEN_HEIGHT		= 350;

	static	final	int	SLIDER_NUM			= 1;
	static	final	int	BUTTON_NUM			= 2;

	static 	final 	int	OVERVIEW_REFRESH_RATE = 20;

	// final int
	int overview_draw_count;

	// CLASS VARIABLES
	//float[] clearZ;
	Camera		theCamera;
	Controls	controls;
	Overview 	overview;
	GroundPlane groundPlane;
	ObjectManager objectManager;
	MovingObjectsManager movingObjectsManager;
	Building selectedBuilding;
	Building lastSelectedBuilding;
	OBJModel	letters;
	Boxes		boxes;
	PGraphics3	g3;
	static PImage overviewImage;
	public Environment env;
	NaturalFactor naturalFactor;
	// Houses houses;
	public Locator locator;
	//private Button detailButton;

	float letterRotX, letterRotY, letterRotZ;
	private float buildingUniScale = 1;
	private float buildingDatabaseScale = 1;

	int initedObjCounter = 0;

	// camera path notify values
	int notifyInterval;
	int intervalBegin;

	// touring variable for disabling the controls etc
	private boolean returnChanges;
	private boolean preparingForTouring=false;
	private boolean showroom=false;
	private int numStepsForPreparing = 10;
	private int preparingIndex=0;
	private FVector pos2Fly2AfterPrep;
	private boolean touring=false;
	boolean notify = false;
	boolean inited = false;
	boolean drawDebugSpheres = false;
	boolean objectsGoForTheMouse = true;

	// 2D Box variables.. car taken out.
	// float boxX, boxY, boxAngle;
	// boolean carClockwise = false;

	int runNum;

	PFont myFont;
	PImage overlay;

	int ropeSlideIndex=0;

	int detailBraceIndex = 0;
	int detailBraceMulti=1;

	// temp for testing
	public	Vector		spheres;

	public void setup() {
		System.out.println("before");
		size(SCREEN_WIDTH,SCREEN_HEIGHT, P3D);
		System.out.println("afterwards");
		// create the empty zbuffer array and fill it

		  //clearZ=new float[width*height];
		  //for (int i = 0; i < width*height;i++) clearZ[i] = 1.0f;

		g3 = (PGraphics3) g;
		// ? does this help ? ((PGraphics3)g).triangle.setCulling(true);
		runNum=0;

		groundPlane = new GroundPlane(this);
		returnChanges = true;

		theCamera	= new Camera(this);
		controls	= new Controls(this, theCamera);
		theCamera.moveToNow(new FVector(-500.51917f, 951.8057f, 200));
		theCamera.lookAtNow(new FVector(-500.51917f, 851.8057f, 0));

		// Camera Rotate Reference
		//letters = new OBJModel(this);

		naturalFactor = new NaturalFactor(this);

		// houses = new Houses(this, 100);

		// Model Architecture
		letterRotX=0;
		letterRotY=0;
		letterRotZ=0;

		boxes = new Boxes(this);

		buildingUniScale=1;
		objectManager = new ObjectManager(this, theCamera);
		movingObjectsManager = new MovingObjectsManager(this);

		loadPixels();

		locator = new Locator(this);

		noLoop();


		/* GUI-Elements
		detailButton = new Button(450, 300, 30, 30, "detailModusButton.gif", new int[] {0,0}, false, "Beenden des Detailmodus");
		detailButton.setVisible(false);
		*/

		// temp for testing
		spheres = new Vector();


	}

	public void startDrawing(){
		//System.out.println("startDrawing");
		loop();
	}

	public void setNotifyInterval(int p_interval){
		notifyInterval = p_interval;
		intervalBegin=millis();
		notify=true;
	}

	public void setNotify(boolean p_notify){
		notify=p_notify;
	}

	public void setBuildingUniformScale(float scaleVal){
		buildingUniScale = scaleVal;
	}

	public float getBuildingUniformScale(){
		return buildingUniScale;
	}

	public void setBuildingDatabaseScale(float scaleVal){
		buildingDatabaseScale = scaleVal;
	}

	public float getBuildingDatabaseScale(){
		return buildingDatabaseScale;
	}

	public void draw() {

		runNum++;
		//System.out.println("runNum:"+runNum);
		//env.addThem();
		if(runNum>1){
			sphere(50);
			if (runNum<4)
				ortho(-1500, 800, -800, 800, 1000, 2000);
			else returnChanges = theCamera.apply();

			if (runNum == 4) {
				theCamera.lookAtNow(new FVector(-500.51917f, 851.8057f, 0));
				theCamera.lookAtInter(new FVector(1341.8213f, 757.865f, 0), new Integer(4000), new Integer(3));
				Object[] actionObjects = {new FVector(0, 0, -88), new Integer(2000), new Integer(0)};
				theCamera.queueAction("lookAtInter", 4000, actionObjects);
				theCamera.moveToNow(new FVector(-500.51917f, 951.8057f, 200));
				theCamera.moveToInter(new FVector(1341.8213f, 857.865f, 200), new Integer(4000), new Integer(3));
				Object[] actionObjects2 = {new FVector(0, 800, Camera.maxCameraHeight), new Integer(2000), new Integer(1)};
				theCamera.queueAction("moveToInter", 0, actionObjects2);
				Object[] actionObjects3 = {Boolean.valueOf(true)};
				theCamera.queueAction("setControlsEnabled", 3500, actionObjects3);
			}

			// test world Objects with screen frustum
			if (runNum>2) {
				if (returnChanges) {
					for(int i = 0;i < objectManager.worldObjects.size(); i++) {
						if (((ObjectOfInterest)(objectManager.worldObjects.elementAt(i))).selectable)
							((Building)(objectManager.worldObjects.elementAt(i))).testIfOnScreen();
					}
				}
			}

			//process moving objects
			movingObjectsManager.process();
			// draw time and weather conditions
			naturalFactor.putIntoEffect();

			// korrdinatensystem
			groundPlane.draw(touring);
			clearZBuffer();

			// houses.draw();



			/**
			 * Drawing of Overview window
			 */
	// boxes campusmap logo test
			boxes.draw();

			locator.draw();

			if(touring){
				if(preparingForTouring){
					detailPreparingStep();
//					System.out.println("Touring");
				}
			}


	  if(theCamera.getPos()!=null) Overview.setLookPoint(theCamera.getPos());
	 /* overview.setControlPoints(new FVector[]{ theCamera.m_vControlPoints[0],
	 * theCamera.m_vControlPoints[1], theCamera.getOriginPos(),
	 * theCamera.getTargetPos() }); }
	 */

			pushMatrix();
			scale(buildingUniScale);
			// draw models (not the transparent ones
			for(int i = 0;i < objectManager.worldObjects.size(); i++) {
				if (((ObjectOfInterest)(objectManager.worldObjects.elementAt(i))).selectable) {
					((Building)(objectManager.worldObjects.elementAt(i))).draw(this, touring);
				}
				else ((ObjectOfInterest)(objectManager.worldObjects.elementAt(i))).draw(this, touring);
			}
			popMatrix();

			// draw moving objects
			movingObjectsManager.draw();

			if (drawDebugSpheres) {
				sphereDetail(20);
				for(int i = 0;i < objectManager.worldObjects.size(); i++) {
					if (((ObjectOfInterest)(objectManager.worldObjects.elementAt(i))).selectable)
						((Building)(objectManager.worldObjects.elementAt(i))).debugDraw(this);
				}
			}


			fill(255,255,255,255);
			noStroke();

			naturalFactor.applySurroundings();

			// 2nd draw models, only for transparent ones
//			for(int i = 0;i < objectManager.worldObjects.size(); i++) {
//				if (((ObjectOfInterest)(objectManager.worldObjects.elementAt(i))).selectable && ((Building)(objectManager.worldObjects.elementAt(i))).myAlpha < 1.0f) {
//					pushMatrix();
//					scale(buildingUniScale);
//					((Building)(objectManager.worldObjects.elementAt(i))).draw(this, false);
//					popMatrix();
//					break;
//				}
//			}
			if(selectedBuilding!=null){
				pushMatrix();
				scale(buildingUniScale);
				selectedBuilding.draw(this, false);
				popMatrix();
			}


			// clearZBuffer();
			hint(DISABLE_DEPTH_TEST);

			//sphere list drawing for testing
			for (int i = 0; i< spheres.size(); i++){
				FVector pos = ((FVector)(spheres.elementAt(i))).cloneMe();
				fill(255, 100, 100, 255);
				pushMatrix();
				translate(pos.getX(), pos.getY(), pos.getZ());
				sphere(10+5*i);
				popMatrix();
			}

			//updatePixels();

			/*
			 * if (overviewImage != null) { for (int y = 0, y2 = 0; y <
			 * (overviewImage.height * width); y += width, y2 +=
			 * overviewImage.width) { for (int x = width - overviewImage.width,
			 * x2 = 0; x < width; x++, x2++) { //System.out.println("" + (x + y) + " " +
			 * (x2+y2)); pixels[y+x] = 0xff000000 + color( (red(pixels[y+x]) +
			 * red (overviewImage.pixels[y2+x2]))/2, (green(pixels[y+x]) + green
			 * (overviewImage.pixels[y2+x2]))/2, (blue(pixels[y+x]) +
			 * blue(overviewImage.pixels[y2+x2]))/2); } } }
			 */

			// 2D interface (buttons and sliders so far)
			camera(); // reset camera
			//lights();

			// The second time all gets painted
			if (runNum==3){
				try{
					noStroke();
					getOverviewShut();
					env.addThem();

					int bgWidth = env.bgImg.getWidth(env);
					int bgHeight =  env.bgImg.getHeight(env);
					int[] bgPixels = new int[bgWidth*bgHeight];
					PixelGrabber pg = new PixelGrabber(env.bgImg, 0, 0, bgWidth, bgHeight, bgPixels, 0, bgWidth);
					for(int i=0;i<bgPixels.length;i++)
						if(bgPixels[i]!=0) System.out.print(bgPixels[i]);
					overlay = new PImage(bgPixels, bgWidth, bgHeight, ARGB);//loadImage(Environment.address+Environment.ressourceFolder+"arrows01.png");
				}catch(Throwable t){
					env.setErrorDisplay("Das Applet konnte nicht gestartet werden. Eventuell ist dies ein Speicherproblem. Bitte stoppen sie alle anderen Java-Anwendungen. GGf. muss der Browser neu gestartet werden um den Cache zu leeren.");
				}
			}


			// rotating thing to rotate camera - has to get its own class
			// ****************************************
			fill(0, 20, 150, 20);
			stroke(255,0,0);
			noFill();

			if(runNum>3)
			// render SlideCases
			// ****************************************
			renderSlideCases(mouseX, mouseY);
			// **************************************

			if(touring){
				paintTouringRope();
				drawDetailBraces();
			}

			if(overlay!=null){
//				System.out.println("bgImage groesse: "+bgPixels[50]);
				image(overlay, 0, 250);
			}

			// lights();
			// buttons.evaluate(mouseX, mouseY, controls.mouseJustReleased,
			// controls.mouseJustPressed);
			// buttons.draw(this);

			theCamera.drawFramerate();



			// clean up and similar
			// *****************************************************
			controls.reset();
			env.repaintEnv();

		}

		// wait delay to allow processing of other system tasks
		delay(30);
		noHint(DISABLE_DEPTH_TEST);
	}

	public void getOverviewShut(){
		updatePixels();
		int[] buffer = new int[pixels.length];
		System.arraycopy(pixels, 0, buffer, 0, pixels.length);
		overviewImage=new PImage(buffer, width, height, RGB);
		// overviewImage.save("image.tif");
		System.out.println("shut for overview");
	}

	private void drawDetailBraces(){
		stroke(255,0,0);
		pushMatrix();
		translate(285, 175);
		scale(1-(float)(detailBraceIndex*0.01));
		line(-250,-150,-240,-150);
		line(-250,-150,-250,150);
		line(-250,150,-240,150);
		line(250,-150,240,-150);
		line(250,-150,250,150);
		line(250,150,240,150);
		popMatrix();
		detailBraceIndex+=detailBraceMulti;
		if(detailBraceIndex>5||detailBraceIndex<0)detailBraceMulti*=-1;
	}

	public void prepareForDetailDraw(FVector pos2Fly2AfterPrep_p, boolean showroom_p){
		pos2Fly2AfterPrep=pos2Fly2AfterPrep_p;
		showroom =showroom_p;
		if(lastSelectedBuilding!=selectedBuilding){
			preparingForTouring=true;
			objectManager.resetBuildings();
			detailPreparingStep();
		} else readyPrepared();
	}

	public void prepareForNextBuildingDetail(FVector pos2Fly2AfterPrep_p, boolean showroom_p){
		pos2Fly2AfterPrep=pos2Fly2AfterPrep_p;
		showroom =showroom_p;
		if(lastSelectedBuilding!=selectedBuilding){
			preparingForTouring=true;
			objectManager.resetBuildings();
			detailPreparingStep();
		} else readyPrepared();
	}

	private void detailPreparingStep(){
		if(preparingIndex<=numStepsForPreparing){
			float currAlpha = 1.0f - ((1.0f/numStepsForPreparing )* preparingIndex);
			System.out.println("Alpha="+currAlpha);
			for(int i = 0;i < objectManager.worldObjects.size(); i++) {
				Object currObject = objectManager.worldObjects.elementAt(i);
				if (currObject instanceof Building && !currObject.equals(selectedBuilding)) {
						((Building)(objectManager.worldObjects.elementAt(i))).setAlpha(currAlpha);
				}
			}
			preparingIndex++;
		}else{
			objectManager.makeBuildingsInvisible(selectedBuilding);
			preparingForTouring=false;
			preparingIndex=0;
			readyPrepared();
		}
	}

	private void readyPrepared(){
		System.out.println("ready!");
		env.theContent.theCamera.flyToRoom(selectedBuilding, pos2Fly2AfterPrep, showroom);
	}

	public void setTouring(boolean p_touring, Building selectedBuilding_p){
		selectedBuilding = selectedBuilding_p;
		controls.setEnabled(!p_touring);
		SlideCase.setActive(!p_touring);

		touring = p_touring;
		ropeSlideIndex=0;
		if(touring){
			Environment.contentHolder.setBorder(new LineBorder(Environment.fh_CI_Color, 3));
			hideSlideCases();
		} else {
			theCamera.setInstantCircleView(Boolean.valueOf(false));
			theCamera.setLastCircleViewBuilding(null);
			Object[] actionObjects = {};
			theCamera.queueAction("resetBuildings", 1000, actionObjects);
			theCamera.guaranteeMinInteractiveCameraHeight();

			Environment.contentHolder.setBorder(Environment.grayline);
		}
	}
	
	public boolean isTouring() {
		return touring;
	}

	public void hideSlideCases(){
		for(int scHideIndex=0;scHideIndex<objectManager.drawers.size();scHideIndex++)
			if(((SlideCase)objectManager.drawers.get(scHideIndex)).getState()!=SlideCase.SHIFTED_IN){
					if(((SlideCase)objectManager.drawers.get(scHideIndex)).isSliding())
						((SlideCase)objectManager.drawers.get(scHideIndex)).requestSlide();
					else ((SlideCase)objectManager.drawers.get(scHideIndex)).slide();
			}
	}

	private void paintTouringRope(){
		noStroke();
		fill(255,255,0);
		rect(0, height-10, width, 10);
		fill(0,0,0);
		pushMatrix();
		translate(-20+ropeSlideIndex,height-10);
		for(int ropeCountIndex=0;ropeCountIndex<30; ropeCountIndex++){
			beginShape();
			vertex(0, 0);
			vertex(10, 0);
			vertex(15, 10);
			vertex(5, 10);
			endShape();
			translate(20,0);
		}
		popMatrix();
		ropeSlideIndex++;
		if(ropeSlideIndex>20)ropeSlideIndex=1;

//		detailButton.check(mouseX, mouseY, controls.mouseJustPressed, controls.mouseJustReleased);
//		detailButton.draw(this);
	}

	public void renderSlideCases(int mouseX, int mouseY){
		// ObjectManager slideCases
		tint(200,10,10,200);
		for(int caseIndex=0;caseIndex<objectManager.drawers.size(); caseIndex++){
			// Get Values from SlideCase
			((SlideCase)objectManager.drawers.get(caseIndex)).evalMouse(mouseX, mouseY, mousePressed, controls.mouseJustReleased);
			((SlideCase)objectManager.drawers.get(caseIndex)).draw();

			// Pixels existing?
			if(((SlideCase)objectManager.drawers.get(caseIndex)).pixels!=null){
				FVector imgPos = ((SlideCase)objectManager.drawers.get(caseIndex)).getPos();
				// System.out.println("left2: "+imgPos.getX());
				PImage img = ((SlideCase)objectManager.drawers.get(caseIndex)).getPixels();
				image(img, imgPos.getX(), imgPos.getY());
			}// if Pixels exist
			else System.out.println("No Pixels!");
		}// for
//		tint(255,255,255,200);
	}

	// public void paint(){/**/}


	  public void clearZBuffer() {
		  for (int i = 0; i< g3.zbuffer.length; i++)
			  if (g3.zbuffer[i]<=1.0f)
				  g3.zbuffer[i] = 1.0f;
		  //System.arraycopy(clearZ,0,g3.zbuffer,0,clearZ.length);
	  }


	/*
	 * void updateBox() { // Draw a box that rotates in a circle pushMatrix();
	 * if(carClockwise) { boxAngle += 0.1; if(round(boxX/5)*5 == 0 &&
	 * round(boxY/5)*5 == 0) { carClockwise = false; boxX = 0; boxY = 0; } }
	 * else { boxAngle -= 0.1; if(round(boxX/5)*5 == 0 && round(boxY/5)*5 == 0) {
	 * carClockwise = true; boxX = 0; boxY = 0; } } boxX += 10 * cos(boxAngle);
	 * boxY += 10 * sin(boxAngle); translate(boxX, boxY, 0); rotateZ(boxAngle);
	 * translate(0,-50,0); noStroke(); fill(255, 0, 0); box(50, 30, 20);
	 * popMatrix(); }
	 */

	/**
	 *
	 * @param holder
	 */
	public void setEnvironment(Environment holder)
	{
		env=holder;
	}

//	public void findRoom(int buildingNo, int levelNo, int roomNo) {
//		try {
//			Building	building		= (Building)objectManager.buildingReferences[buildingNo-1];
//			//no more rooms, now all in database! Room		room			=  building.myRooms[levelNo][roomNo-1];
//			FVector		centerPosition	=  building.myPos.multiply(buildingUniScale);
//			centerPosition.setZ(building.flyAroundCenterHeight);
//			theCamera.flyToRoomInBuilding(new FVector(140, 200, 16), centerPosition, building.flyAroundRadius, 2000, 1200);
//		} catch (NullPointerException nexp) {
//			System.err.println("wrong room number or building number " + nexp);
//		} catch (ArrayIndexOutOfBoundsException aexp) {
//			System.err.println("wrong room number or building number " + aexp);
//		}
//	}

	// Mouse and Key Envents processed in Controls
	public void mouseDragged() {
		controls.mouseDragged();
	}
	public void mouseMoved() {
		controls.mouseMoved();
	}
	public void mousePressed() {
		controls.mousePressed();
	}
	public void mouseReleased() {
		controls.mouseReleased();
	}
	public void keyPressed() {
		controls.keyPressed();
	}
	public void keyReleased() {
		controls.keyReleased();
	}

	public void sayHello() {
		System.out.println("Hello");
	}


}

