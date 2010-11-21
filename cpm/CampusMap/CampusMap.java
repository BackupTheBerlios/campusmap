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
        StreamingManager streamingManager;
	Building selectedBuilding;
	Building lastSelectedBuilding;
	OBJModel	letters;
	Boxes		boxes;
	PGraphics3D	g3;
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
        boolean afterFirstStreaming=false;
        boolean afterOverviewShot=false;
        boolean afterIntro = false;
        boolean inited = false;
	boolean drawDebugSpheres = false;
	boolean objectsGoForTheMouse = true;

	// 2D Box variables.. car taken out.
	// float boxX, boxY, boxAngle;
	// boolean carClockwise = false;

	int runNum;

	PImage overlay;
        StreamingPicture slideIcon;
        StreamingFont myFont;

	int ropeSlideIndex=0;

	int detailBraceIndex = 0;
	int detailBraceMulti=1;

	// temp for testing
	public	Vector		spheres;

	public void setup() {
          //everything that happens before Streaming invokation
          preStreamSetup();

          //other setups are called after streaming of min lod-level and intro camera tour

          noLoop();
	}

        private void preStreamSetup(){
          size(SCREEN_WIDTH, SCREEN_HEIGHT, P3D);
          g3 = (PGraphics3D) g;
          // ? does this help ? ((PGraphics3)g).triangle.setCulling(true);
          groundPlane = new GroundPlane(this);
          returnChanges = true;
          spheres = new Vector();
          slideIcon = new StreamingPicture(this, "arrow.gif");
          myFont = new StreamingFont(this, "CenturyGothic-14.vlw.gz");
          Vector importantObjects = new Vector();
          importantObjects.add(slideIcon);
          importantObjects.add(myFont);

          theCamera = new Camera(this);
          controls = new Controls(this, theCamera);
          naturalFactor = new NaturalFactor(this);
          boxes = new Boxes(this);
          locator = new Locator(this);
          objectManager = new ObjectManager(this, theCamera);

          streamingManager = new StreamingManager(this, objectManager.worldObjects, importantObjects);
          movingObjectsManager = new MovingObjectsManager(this);
        }

        public void preIntroSetup(){
          try {

            int bgWidth = env.bgImg.getWidth(env);
            int bgHeight = env.bgImg.getHeight(env);
            int[] bgPixels = new int[bgWidth * bgHeight];
            for (int i = 0; i < bgPixels.length; i++)
              if (bgPixels[i] != 0) System.out.print(bgPixels[i]);
            overlay = new PImage(bgWidth, bgHeight);
            overlay.format = ARGB;
            overlay.pixels = bgPixels;
            runNum = 0;
            controls.setEnabled(false);

            /***
             *  get into looping
             */
            afterFirstStreaming=true;
            /**
             *
             * overview shut
            */
            draw();
            /*theCamera.moveToNow(new FVector(0.0f, 0.0f, 1000));
            theCamera.lookAtNow(new FVector(0.0f, 1.0f, 0));
            */
            draw();
            ortho( -1500, 800, -800, 800, 1000, 2000);
            loadPixels();
            noStroke();
            getOverviewShut();
            afterOverviewShot=true;
            draw();
            loop();

            /**
             *  give the environment the hint to swop the surfaces
             */
            env.addThem();
            /***
             * intro
             *
             **/
            theCamera.moveToNow(new FVector( -200.0f, 950.0f, 1000));
            theCamera.lookAtNow(new FVector( -200.51917f, 851.8057f, 0));
            theCamera.lookAtInter(new FVector(1341.8213f, 757.865f, 0),
                                  new Integer(4000), new Integer(3));
            Object[] actionObjects = {
                new FVector(0, 0, -88), new Integer(2000), new Integer(0)};
            theCamera.queueAction("lookAtInter", 4000, actionObjects);
            theCamera.moveToNow(new FVector( -500.51917f, 951.8057f, 200));
            theCamera.moveToInter(new FVector(1341.8213f, 857.865f, 200),
                                  new Integer(4000), new Integer(3));
            Object[] actionObjects2 = {
                new FVector(0, 800, Camera.maxCameraHeight), new Integer(2000),
                new Integer(1)};
            theCamera.queueAction("moveToInter", 0, actionObjects2);
            Object[] actionObjects3 = {
                Boolean.valueOf(true)};
            theCamera.queueAction("setControlsEnabled", 3500, actionObjects3);
           }
          catch (Exception e) {
        	e.printStackTrace();
            env.setErrorDisplay("Das Applet konnte nicht gestartet werden. Eventuell ist dies ein Speicherproblem. Bitte stoppen sie alle anderen Java-Anwendungen. GGf. muss der Browser neu gestartet werden um den Cache zu leeren.");
          }
        }

        public void preInteractivitySetup(){
          System.err.println("Go ahead with streaming please!");
          controls.setEnabled(true);
          this.streamingManager.continueStreamingAfterIntro();
        }

	public void draw() {
          if(!afterFirstStreaming)return;
          runNum++;

          if(afterOverviewShot)returnChanges = theCamera.apply();

          // test world Objects with screen frustum
          if (returnChanges) {
            for (int i = 0; i < objectManager.worldObjects.size(); i++) {
              if ( ( (ObjectOfInterest) (objectManager.worldObjects.elementAt(i))).
                  selectable)
                ( (Building) (objectManager.worldObjects.elementAt(i))).
                    testIfOnScreen();
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
          boxes.draw();

          pushMatrix();
          scale(buildingUniScale);
          // draw models
          for (int i = 0; i < objectManager.worldObjects.size(); i++) {
            if ( ( (ObjectOfInterest) (objectManager.worldObjects.elementAt(i))).
                selectable) {
              ( (Building) (objectManager.worldObjects.elementAt(i))).draw(this,
                  false /* Don't draw in gray old:touring*/);
            }
            else ( (ObjectOfInterest) (objectManager.worldObjects.elementAt(i))).draw(this,
                touring);
          }
          popMatrix();

          /********************
           *  AFTER INTRO
           ****************************************************************************************/
          if(afterIntro){                                                                         //
            if (theCamera.getPos() != null) Overview.setLookPoint(theCamera.getPos());            //
            locator.draw();                                                                       //
                                                                                                  //
            // draw moving objects                                                                //
            movingObjectsManager.draw();                                                          //
                                                                                                  //
            if (drawDebugSpheres) {                                                               //
              sphereDetail(20);                                                                   //
              for (int i = 0; i < objectManager.worldObjects.size(); i++) {                       //
                if ( ( (ObjectOfInterest) (objectManager.worldObjects.elementAt(i))).             //
                    selectable)                                                                   //
                  ( (Building) (objectManager.worldObjects.elementAt(i))).debugDraw(this);        //
              }                                                                                   //
            }                                                                                     //
                                                                                                  //
            fill(255, 255, 255, 255);                                                             //
            noStroke();                                                                           //
                                                                                                  //
            //sphere list drawing for testing                                                     //
            for (int i = 0; i < spheres.size(); i++) {                                            //
              FVector pos = ( (FVector) (spheres.elementAt(i))).cloneMe();                        //
              fill(255, 100, 100, 255);                                                           //
              pushMatrix();                                                                       //
              translate(pos.getX(), pos.getY(), pos.getZ());                                      //
              sphere(10 + 5 * i);                                                                 //
              popMatrix();                                                                        //
            }                                                                                     //
                                                                                                  //
          }                                                                                       //
          /***************************************************************************************
           *  END AFTER INTRO
           ****************************/

          naturalFactor.applySurroundings();

          if (selectedBuilding != null) {
            pushMatrix();
            scale(buildingUniScale);
            selectedBuilding.draw(this, false);
            popMatrix();
          }

          // clearZBuffer();
          hint(DISABLE_DEPTH_TEST);

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

          fill(0, 20, 150, 20);
          stroke(255, 0, 0);
          noFill();

          // render SlideCases
          renderSlideCases(mouseX, mouseY);

          if (touring) {
            if (preparingForTouring) {
              detailPreparingStep();
            }else{
              paintTouringRope();
              drawDetailBraces();
            }
          }

          if (overlay != null) {
            image(overlay, 0, 250);
          }

          theCamera.drawFramerate();

          // clean up and similar
          // *****************************************************
          controls.reset();
          env.repaintEnv();

          // wait delay to allow processing of other system tasks
          try
          {
        	  delay(30);
          }catch(java.lang.IllegalMonitorStateException e)
          {
//        	  e.printStackTrace();
          }
          noHint(DISABLE_DEPTH_TEST);
	}

	public void getOverviewShut(){
		updatePixels();
		int[] buffer = new int[pixels.length];
		System.arraycopy(pixels, 0, buffer, 0, pixels.length);
		overviewImage = new PImage(width, height);
		overviewImage.format = RGB;
		overviewImage.pixels = buffer;
		// overviewImage.save("image.tif");
                buffer=null;
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

        /**
         *  Global entrance/exit into/from detailview
         */
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


          public boolean isTouring() {
                  return touring;
          }

	/**
	 *
	 * @param holder
	 */
	public void setEnvironment(Environment holder)
	{
		env=holder;
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

