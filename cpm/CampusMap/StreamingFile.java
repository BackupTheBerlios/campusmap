package CampusMap;

import processing.core.PApplet;
import processing.core.PImage;
import processing.core.PFont;

/**
 * <p>Title: StreamingFile</p>
 *
 * <p>Description: Defines a general Class that wraps loading Functionality around some random File</p>
 *
 * <p>Copyright: Copyright (c) 2007</p>
 *
 * @author: David Hübner, Gunnar Dröge
 *
 */
class StreamingFile extends Thread {

        boolean done = false;

        public StreamingFile(){
          done = false;
          setPriority(Thread.MIN_PRIORITY);
        }
        public void run() {
                 done = true;
        }
        public boolean isDone() {
                return done;
        }
}

class StreamingModel extends StreamingFile {

  int lodToLoad = 0;
  ObjectOfInterest fileToLoad;

  public StreamingModel(ObjectOfInterest p_modelToLoad, int p_lodToLoad) {
    super();
    fileToLoad = p_modelToLoad;
    lodToLoad = p_lodToLoad;
  }

  public void run() {
    try {
      ( (ObjectOfInterest) fileToLoad).setLodModelBeingLoaded(
                        lodToLoad, true);
      ( (ObjectOfInterest) fileToLoad).loadModel(lodToLoad);
      super.run();
      System.out.println("Loading of file "+fileToLoad.modelsToLoad[lodToLoad]+" is done");
    }
    catch (Exception ex) {
      System.err.print("Modelloading failed at one point.");
    }
  }
}

class StreamingPicture extends StreamingFile {

  String fileToLoad;
  PApplet applet;
  PImage target;

  public StreamingPicture(PApplet applet_p, String p_modelToLoad){
    super();
    applet = applet_p;
    fileToLoad = p_modelToLoad;
  }

  public void run() {
    target = applet.loadImage(Environment.address+Environment.ressourceFolder+fileToLoad);
    super.run();
    System.out.println("Loading of file "+fileToLoad+" is done");
  }
}

class StreamingFont extends StreamingFile {

  String fileToLoad;
  PApplet applet;
  PFont font;


  public StreamingFont(PApplet applet_p, String p_fileToLoad){
    super();
    applet = applet_p;
    fileToLoad = p_fileToLoad;
  }

  public void run() {
    font = applet.loadFont( Environment.address+Environment.ressourceFolder + fileToLoad);
    super.run();
    System.out.println("Loading of file "+fileToLoad+" is done");
  }
}

