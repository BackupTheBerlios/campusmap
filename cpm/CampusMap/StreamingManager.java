package CampusMap;

import java.util.*;
import processing.core.PApplet;
//import javax.swing.*;

/** Class to manage all streaming elements that get loaded into the applet while it is running.
 *  -Models
 *  -...
 */
public class StreamingManager extends Thread {

	static	final	int	SIMULTAN_FILES		= 3;
	static	final	int	MAX_LOD_LEVEL		= 2;
        static  final   int     URGENT_FILE             = 1;
        static  final   int     INIT_MIN_LOD            = 1;
	PApplet applet;
	Vector worldObjects;
        StreamingFile urgentModelToLoad;
	Vector streamingFiles[];
        StreamingFile slots[];
//        int currLoadingPointer=0;
	int numLoadedFiles = 0;
	int numFilesToLoad = 0;
	int numObjectsToLoad = 0;
	int initedObjCounter = 0;
	int lodToLoad = 0;
        int waitingTime = 300;                          // waiting time to be extended afterwards loading the main models. Afterwards we need performance
        boolean streamingPausedForIntro=false;
        boolean someModelNOTLoaded=true;
	boolean load2Loaded = false;
	boolean initMinLodReached;
	//JFrame ladeFenster;

	public StreamingManager(PApplet p_applet, Vector p_worldObjects, Collection otherImportantObjects) {
		applet = p_applet;
		((CampusMap)applet).env.objectInitDisplay.setText("StreamingManager");
		initMinLodReached=false;
		worldObjects = p_worldObjects;
		numObjectsToLoad = worldObjects.size();
		numFilesToLoad = findNumberOfFilesForWorldObj();
        streamingFiles = new Vector[MAX_LOD_LEVEL];
        for (int i=0; i<MAX_LOD_LEVEL; i++)
          streamingFiles[i] = new Vector();
        streamingFiles[0].addAll(otherImportantObjects);
        for (int i=0; i<worldObjects.size(); i++)
          for(int lodIndex=0;
              lodIndex<((ObjectOfInterest)worldObjects.get(i)).getNumberOfLodModels() &&
              lodIndex<MAX_LOD_LEVEL;
              lodIndex++){
            streamingFiles[lodIndex].add(new StreamingModel(
                (ObjectOfInterest)worldObjects.get(i), lodIndex));
          }
        slots = new StreamingFile[SIMULTAN_FILES];
		start();
	}

	private int findNumberOfFilesForWorldObj() {
		int number = 0;
		for (int i = 0; i < numObjectsToLoad; i++) {
			number += ((ObjectOfInterest)worldObjects.elementAt(i)).getNumberOfLodModels();
		}
		return number;
	}

        public void continueStreamingAfterIntro(){
          streamingPausedForIntro=false;
        }

        public void setUrgendModelToLoad(ObjectOfInterest urgentModelToLoad_p){
          // stop stop, has this model so much lods?!
          try {
            if (urgentModelToLoad_p.getNumberOfLodModels() >= MAX_LOD_LEVEL) {
              urgentModelToLoad = new StreamingModel((ObjectOfInterest)urgentModelToLoad_p, MAX_LOD_LEVEL);
            }else System.err.println("Doesn't have so much detail");
          }catch (NullPointerException npex) {}
        }

	private void findNewFileToLoad(int slot) {
          int fileNo = 0;
          int arrayPointer=0;
          int arrayLod=0;
          int currLoadingPointer=0;
          boolean foundModel = false;
          /**
           *  First check if urgent model to load somewhere (presumably will be building clicked)
           */
          if(urgentModelToLoad!=null){
            slots[slot] = urgentModelToLoad;
            urgentModelToLoad=null;
            try {
              slots[slot].start();
              System.out.println("urgentModel!");
            }
            catch (Exception ex) {}
          } else {
            /**
             *  ELse check on all lods if there's something left
             */
            someModelNOTLoaded=false;
            while(!someModelNOTLoaded && arrayLod < MAX_LOD_LEVEL){
              if(arrayLod < MAX_LOD_LEVEL && (
                  !( (StreamingFile) streamingFiles[arrayLod].get( arrayPointer)).isDone() &&
                  !( (StreamingFile) streamingFiles[arrayLod].get( arrayPointer)).isAlive() ) ){
                currLoadingPointer=arrayPointer;
                lodToLoad=arrayLod;
                someModelNOTLoaded=true;
              }
              arrayPointer++;
              if(arrayPointer >= streamingFiles[arrayLod].size()){
                arrayLod++;
                arrayPointer=0;
              }
             }
            }

            /**********************\
             *  Invoke of drawing
            \**********************/
            if(!((CampusMap)applet).afterFirstStreaming && lodToLoad==INIT_MIN_LOD){
              System.err.println("Level " + lodToLoad + " loaded");
              // if this was the initialisation level
              streamingPausedForIntro = true;
              ( (CampusMap) applet).preIntroSetup();
            }
            if(!streamingPausedForIntro){
	            if(someModelNOTLoaded){
	              // set display message and invoke loading
	              /*                  ( (CampusMap) applet).env.objectInitDisplay.setText(
	                                    ((ObjectOfInterest)streamingFiles[lodToLoad].get(currLoadingPointer)).modelsToLoad[lodToLoad]);
	               */
	              System.out.println("load Level " + lodToLoad +
	                                 " with length " + streamingFiles[lodToLoad].size() +
	                                 " and model "+(currLoadingPointer)+
	                                 " at slot: "+slot);
	              slots[slot] =
	                  ( (StreamingFile) streamingFiles[lodToLoad].get( currLoadingPointer));
	
	              try {slots[slot].start();}
	              catch (IllegalThreadStateException ex) {
	                ex.printStackTrace();
	              }
	            } else {
	              // no model currently to load!
	              // setting down loading check interval
	              waitingTime = 2000;
	            }
            }
	}

	private Vector leftToLoadInThisLod() {
		Vector rueckgabe=new Vector();
		for (int testIndex=0; testIndex < numObjectsToLoad; testIndex++) {
			// if all others return "loaded"
			if (((ObjectOfInterest)(worldObjects.elementAt(testIndex))).getNumberOfLodModels()>lodToLoad &&
				(!((ObjectOfInterest)(worldObjects.elementAt(testIndex))).getLodModelBeingLoaded(lodToLoad)) &&
				(!((ObjectOfInterest)(worldObjects.elementAt(testIndex))).getLodModelLoaded(lodToLoad)) ){
				rueckgabe.add(worldObjects.elementAt(testIndex));
			}else{
				numLoadedFiles++;
			}
		}
//		System.out.println("arraygroesse"+rueckgabe.size());
		return rueckgabe;
	}

	public void run() {
		((CampusMap)applet).env.initDisplay.setText("loading geometry files");
                // runs all the time as we want to load models on runtime
		while (true) {
                  if(!streamingPausedForIntro)
                    try {
//			System.out.println("load lods loop started: " + numLoadedFiles + " of " + numFilesToLoad + " files loaded.");
                      for (int i = 0; i < SIMULTAN_FILES; i++) {
                         if (slots[i] == null ||
                             slots[i].isDone() ){
                           //System.out.println("slot " + i + " is empty and can now be used.");
                           findNewFileToLoad(i);
                         }
                       }
                       Thread.sleep(waitingTime);
                  } catch(InterruptedException ie) {
                    System.err.println("Insomnia @ StreamingManager");
                  }
		}
		//Environment.setToolTip("Geometrie komplett geladen.", 0);
		// Commented because overrides error message if internet connection doesn't work
	}
}
