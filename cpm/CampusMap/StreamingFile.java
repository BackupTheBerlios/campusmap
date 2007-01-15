package CampusMap;

import processing.core.PApplet;
import processing.core.PImage;

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
        boolean notify= false;

        public StreamingFile(){
          done = false;
          setPriority(Thread.MIN_PRIORITY);
        }
        public void registerNotify(){
                notify=true;
        }
        public void run() {
                 done = true;
        }
        public boolean isDone() {
                return done;
        }
}

interface IStreamingFile
{
  public void notifyInitedObj(int lod);
}

class StreamingModel extends StreamingFile {

  int lodToLoad = 0;
  IStreamingFile fileToLoad;

  public StreamingModel(IStreamingFile p_modelToLoad, int p_lodToLoad) {
    super();
    fileToLoad = p_modelToLoad;
    lodToLoad = p_lodToLoad;
  }

  public void run() {
    if(notify)fileToLoad.notifyInitedObj(lodToLoad);
    try {
      ( (ObjectOfInterest) fileToLoad).setLodModelBeingLoaded(
                        lodToLoad, true);
      ( (ObjectOfInterest) fileToLoad).loadModel(lodToLoad);
      super.run();
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
  }
}

class StreamingFont extends StreamingFile {

  IStreamingFile fileToLoad;
  PApplet target;

  public StreamingFont(PApplet target_p, IStreamingFile p_modelToLoad){
    super();
    fileToLoad = p_modelToLoad;
  }

  public void run() {
    target.loadFont(Environment.address+Environment.ressourceFolder+fileToLoad);
    super.run();
  }
}

